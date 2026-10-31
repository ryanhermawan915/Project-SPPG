<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceRecord;
use App\Models\WorkSchedule;
use Carbon\CarbonPeriod;

class LeaveRequestController extends Controller
{
    /**
     * Menampilkan seluruh pengajuan izin dan sakit.
     */
    public function index(Request $request)
    {
        $admin = $request->user();

        // Pastikan hanya admin aktif yang dapat mengakses.
        if (
            $admin->role !== 'admin' ||
            $admin->account_status !== 'active'
        ) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        // Validasi filter.
        $validated = $request->validate([
            'status' => 'nullable|in:pending,approved,rejected',
            'request_type' => 'nullable|in:permission,sick',
        ]);

        // Ambil seluruh pengajuan beserta data pegawai.
        $query = LeaveRequest::with([
            'user.employeeProfile',
            'reviewer',
        ]);

        // Filter berdasarkan status.
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // Filter berdasarkan jenis pengajuan.
        if (!empty($validated['request_type'])) {
            $query->where(
                'request_type',
                $validated['request_type']
            );
        }

        // Data terbaru ditampilkan lebih dahulu.
        $requests = $query
            ->latest()
            ->paginate(10);

        // Format respons API.
        $requests->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,

                'employee' => [
                    'id' => $item->user?->id,
                    'name' => $item->user?->name,
                    'email' => $item->user?->email,
                    'nip' => $item->user?->employeeProfile?->employee_number,
                    'position' => $item->user?->employeeProfile?->position,
                ],

                'request_type' => $item->request_type,
                'start_date' => $item->start_date?->format('Y-m-d'),
                'end_date' => $item->end_date?->format('Y-m-d'),
                'reason' => $item->reason,

                'has_attachment' => !empty($item->attachment_path),

                'status' => $item->status,
                'review_notes' => $item->review_notes,

                'reviewer' => $item->reviewer?->name,
                'reviewed_at' => $item->reviewed_at,

                'created_at' => $item->created_at,
            ];
        });

        return response()->json([
            'message' => 'Data pengajuan berhasil diambil.',
            'data' => $requests,
        ]);
    }

    
/**
 * Menampilkan detail satu pengajuan.
 */
public function show(Request $request, $id)
{
    $admin = $request->user();

    // Pastikan hanya admin aktif yang dapat mengakses.
    if (
        $admin->role !== 'admin' ||
        $admin->account_status !== 'active'
    ) {
        return response()->json([
            'message' => 'Anda tidak memiliki akses.',
        ], 403);
    }

    $leaveRequest = LeaveRequest::with([
        'user.employeeProfile',
        'reviewer',
    ])->findOrFail($id);

    return response()->json([
        'message' => 'Detail pengajuan berhasil diambil.',
        'data' => [
            'id' => $leaveRequest->id,

            'employee' => [
                'id' => $leaveRequest->user?->id,
                'name' => $leaveRequest->user?->name,
                'email' => $leaveRequest->user?->email,
                'nip' => $leaveRequest->user?->employeeProfile?->employee_number,
                'position' => $leaveRequest->user?->employeeProfile?->position,
                'phone' => $leaveRequest->user?->employeeProfile?->phone,
            ],

            'request_type' => $leaveRequest->request_type,
            'start_date' => $leaveRequest->start_date?->format('Y-m-d'),
            'end_date' => $leaveRequest->end_date?->format('Y-m-d'),
            'reason' => $leaveRequest->reason,

            'has_attachment' => !empty($leaveRequest->attachment_path),

            'status' => $leaveRequest->status,
            'review_notes' => $leaveRequest->review_notes,

            'reviewer' => $leaveRequest->reviewer?->name,
            'reviewed_at' => $leaveRequest->reviewed_at,

            'created_at' => $leaveRequest->created_at,
        ],
    ]);
}



/**
 * Menyetujui pengajuan izin atau sakit
 * sekaligus mencatatnya ke data presensi.
 */
public function approve(Request $request, $id)
{
    $admin = $request->user();

    if (
        $admin->role !== 'admin' ||
        $admin->account_status !== 'active'
    ) {
        return response()->json([
            'message' => 'Anda tidak memiliki akses.',
        ], 403);
    }

    $validated = $request->validate([
        'review_notes' => 'nullable|string|max:1000',
    ]);

    $result = DB::transaction(function () use (
        $id,
        $admin,
        $validated
    ) {
        $leaveRequest = LeaveRequest::whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Pengajuan ini sudah diproses sebelumnya.',
            ], 409);
        }

        $attendanceStatus = $leaveRequest->request_type;

        $attendanceSummary = [
            'created' => 0,
            'updated' => 0,
            'skipped_non_working_day' => 0,
            'skipped_existing_attendance' => 0,
        ];

        // Periksa setiap tanggal dalam pengajuan.
        $period = CarbonPeriod::create(
            $leaveRequest->start_date,
            $leaveRequest->end_date
        );

        foreach ($period as $date) {

            $attendanceDate = $date->toDateString();

            // Cari jadwal aktif berdasarkan hari.
            $schedule = WorkSchedule::where(
                'day_of_week',
                $date->dayOfWeekIso
            )
                ->where('is_active', true)
                ->first();

            // Lewati jika bukan hari kerja.
            if (!$schedule) {
                $attendanceSummary['skipped_non_working_day']++;
                continue;
            }

            // Periksa apakah presensi sudah tersedia.
            $attendance = AttendanceRecord::where(
                'user_id',
                $leaveRequest->user_id
            )
                ->where('attendance_date', $attendanceDate)
                ->lockForUpdate()
                ->first();

            // Jangan mengubah presensi yang sudah memiliki
            // aktivitas check-in atau check-out.
            if (
                $attendance &&
                (
                    $attendance->check_in_time !== null ||
                    $attendance->check_out_time !== null
                )
            ) {
                $attendanceSummary['skipped_existing_attendance']++;
                continue;
            }

            $notes = 'Disetujui berdasarkan pengajuan '
                . $attendanceStatus
                . ' #' . $leaveRequest->id;

            if ($attendance) {

                // Perbarui catatan yang belum memiliki
                // aktivitas presensi.
                $attendance->update([
                    'work_schedule_id' => $schedule->id,
                    'status' => $attendanceStatus,
                    'notes' => $notes,
                ]);

                $attendanceSummary['updated']++;

            } else {

                // Buat catatan presensi baru.
                AttendanceRecord::create([
                    'user_id' => $leaveRequest->user_id,
                    'work_schedule_id' => $schedule->id,
                    'attendance_date' => $attendanceDate,
                    'status' => $attendanceStatus,
                    'notes' => $notes,
                ]);

                $attendanceSummary['created']++;
            }
        }

        // Perbarui status pengajuan.
        $leaveRequest->status = 'approved';
        $leaveRequest->reviewed_by = $admin->id;
        $leaveRequest->reviewed_at = now();
        $leaveRequest->review_notes =
            $validated['review_notes'] ?? null;

        $leaveRequest->save();

        return [
            'leave_request' => $leaveRequest,
            'attendance_summary' => $attendanceSummary,
        ];
    });

    // Jika pengajuan sudah pernah diproses.
    if ($result instanceof \Illuminate\Http\JsonResponse) {
        return $result;
    }

    return response()->json([
        'message' => 'Pengajuan berhasil disetujui dan presensi telah diproses.',
        'data' => [
            'id' => $result['leave_request']->id,
            'status' => $result['leave_request']->status,
            'review_notes' => $result['leave_request']->review_notes,
            'reviewer' => $admin->name,
            'reviewed_at' => $result['leave_request']->reviewed_at,
            'attendance_summary' => $result['attendance_summary'],
        ],
    ]);
}

/**
 * Menolak pengajuan izin atau sakit.
 */
public function reject(Request $request, $id)
{
    $admin = $request->user();

    if (
        $admin->role !== 'admin' ||
        $admin->account_status !== 'active'
    ) {
        return response()->json([
            'message' => 'Anda tidak memiliki akses.',
        ], 403);
    }

    $validated = $request->validate([
        'review_notes' => 'required|string|max:1000',
    ]);

    $result = DB::transaction(function () use ($id, $admin, $validated) {

        $leaveRequest = LeaveRequest::whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Pengajuan ini sudah diproses sebelumnya.',
            ], 409);
        }

        $leaveRequest->status = 'rejected';
        $leaveRequest->reviewed_by = $admin->id;
        $leaveRequest->reviewed_at = now();
        $leaveRequest->review_notes = $validated['review_notes'];
        $leaveRequest->save();

        return response()->json([
            'message' => 'Pengajuan berhasil ditolak.',
            'data' => [
                'id' => $leaveRequest->id,
                'status' => $leaveRequest->status,
                'review_notes' => $leaveRequest->review_notes,
                'reviewer' => $admin->name,
                'reviewed_at' => $leaveRequest->reviewed_at,
            ],
        ]);
    });

    return $result;
}

}
