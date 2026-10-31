<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\WorkSchedule;
use App\Services\GeofenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    protected GeofenceService $geofenceService;

    public function __construct(GeofenceService $geofenceService)
    {
        $this->geofenceService = $geofenceService;
    }

    // Presensi berangkat
    public function checkIn(Request $request)
    {
        // 1. Ambil pengguna yang sedang login
        $user = $request->user();

        // 2. Periksa status akun
        if (
            $user->role !== 'employee' ||
            $user->account_status !== 'active'
        ) {
            return response()->json([
                'message' => 'Akun tidak memiliki akses presensi.',
            ], 403);
        }

        // 3. Validasi koordinat GPS
        $validated = $request->validate([
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
        ]);

        // 4. Periksa profil wajah aktif
        $faceProfile = $user->faceProfile()
            ->where('is_active', true)
            ->first();

        if (!$faceProfile) {
            return response()->json([
                'message' => 'Profil wajah belum terdaftar atau tidak aktif.',
            ], 403);
        }

        // 5. Periksa lokasi pegawai
        $geofence = $this->geofenceService->check(
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );

        if (!$geofence['is_inside']) {
            return response()->json([
                'message' => $geofence['message'],
                'data' => $geofence,
            ], 403);
        }

        // 6. Ambil waktu dan hari sekarang
        $now = Carbon::now();
        $today = $now->toDateString();
        $dayOfWeek = $now->dayOfWeek;

        // 7. Cari jadwal kerja aktif untuk hari ini
        $workSchedule = WorkSchedule::where(
            'day_of_week',
            $dayOfWeek
        )
            ->where('is_active', true)
            ->first();

        if (!$workSchedule) {
            return response()->json([
                'message' => 'Tidak ada jadwal kerja aktif untuk hari ini.',
            ], 422);
        }

        // 8. Periksa apakah sudah check-in hari ini
        $existingAttendance = AttendanceRecord::where(
            'user_id',
            $user->id
        )
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            return response()->json([
                'message' => 'Anda sudah melakukan check-in hari ini.',
            ], 409);
        }

        // 9. Hitung batas toleransi keterlambatan
        $startTime = Carbon::parse($workSchedule->start_time);

        $lateDeadline = $startTime->copy()->addMinutes(
            $workSchedule->late_tolerance_minutes
        );

        // 10. Tentukan status sementara
        $attendanceStatus = $now->greaterThan($lateDeadline)
            ? 'late'
            : 'present';

        // 11. Face recognition belum terintegrasi.
        // Belum menyimpan data presensi yang sah.
        return response()->json([
            'message' => 'Pemeriksaan awal check-in berhasil. Verifikasi wajah belum tersedia.',
            'data' => [
                'attendance_recorded' => false,
                'employee_id' => $user->id,
                'attendance_date' => $today,
                'check_in_time' => $now->format('H:i:s'),
                'work_schedule' => [
                    'id' => $workSchedule->id,
                    'name' => $workSchedule->name,
                    'start_time' => $workSchedule->start_time,
                    'end_time' => $workSchedule->end_time,
                    'late_tolerance_minutes' => $workSchedule->late_tolerance_minutes,
                ],
                'attendance_status_preview' => $attendanceStatus,
                'geofence' => $geofence,
                'face_verification' => 'not_integrated',
            ],
        ], 200);
    }

    // Presensi pulang
public function checkOut(Request $request)
{
    // 1. Ambil pengguna yang sedang login
    $user = $request->user();

    // 2. Periksa status akun
    if (
        $user->role !== 'employee' ||
        $user->account_status !== 'active'
    ) {
        return response()->json([
            'message' => 'Akun tidak memiliki akses presensi.',
        ], 403);
    }

    // 3. Validasi koordinat GPS
    $validated = $request->validate([
        'latitude' => [
            'required',
            'numeric',
            'between:-90,90',
        ],
        'longitude' => [
            'required',
            'numeric',
            'between:-180,180',
        ],
    ]);

    // 4. Periksa profil wajah aktif
    $faceProfile = $user->faceProfile()
        ->where('is_active', true)
        ->first();

    if (!$faceProfile) {
        return response()->json([
            'message' => 'Profil wajah belum terdaftar atau tidak aktif.',
        ], 403);
    }

    // 5. Periksa lokasi pegawai
    $geofence = $this->geofenceService->check(
        (float) $validated['latitude'],
        (float) $validated['longitude']
    );

    if (!$geofence['is_inside']) {
        return response()->json([
            'message' => $geofence['message'],
            'data' => $geofence,
        ], 403);
    }

    // 6. Ambil waktu dan tanggal sekarang
    $now = Carbon::now();
    $today = $now->toDateString();

    // 7. Cari catatan check-in hari ini
    $attendance = AttendanceRecord::where('user_id', $user->id)
        ->whereDate('attendance_date', $today)
        ->first();

    if (!$attendance || !$attendance->check_in_time) {
        return response()->json([
            'message' => 'Anda belum melakukan check-in hari ini.',
        ], 409);
    }

    // 8. Periksa apakah sudah check-out
    if ($attendance->check_out_time) {
        return response()->json([
            'message' => 'Anda sudah melakukan check-out hari ini.',
        ], 409);
    }

    // 9. Ambil jadwal kerja
    $workSchedule = $attendance->workSchedule;

    if (!$workSchedule) {
        return response()->json([
            'message' => 'Jadwal kerja untuk presensi ini tidak ditemukan.',
        ], 422);
    }

    // 10. Tentukan waktu selesai jadwal
    $endTime = $now->copy()->setTimeFromTimeString(
        $workSchedule->end_time
    );

    $checkoutTiming = $now->lessThan($endTime)
        ? 'before_schedule_end'
        : 'at_or_after_schedule_end';

    // 11. Face recognition belum terintegrasi.
    // Belum menyimpan data presensi pulang yang sah.
    return response()->json([
        'message' => 'Pemeriksaan awal check-out berhasil. Verifikasi wajah belum tersedia.',
        'data' => [
            'attendance_recorded' => false,
            'employee_id' => $user->id,
            'attendance_date' => $today,
            'check_out_time' => $now->format('H:i:s'),
            'scheduled_end_time' => $workSchedule->end_time,
            'checkout_timing_preview' => $checkoutTiming,
            'geofence' => $geofence,
            'face_verification' => 'not_integrated',
        ],
    ], 200);
}

// Riwayat presensi pegawai
public function history(Request $request)
{
    // 1. Ambil pengguna yang sedang login
    $user = $request->user();

    // 2. Periksa status akun
    if (
        $user->role !== 'employee' ||
        $user->account_status !== 'active'
    ) {
        return response()->json([
            'message' => 'Akun tidak memiliki akses riwayat presensi.',
        ], 403);
    }

    // 3. Validasi filter tanggal
    $validated = $request->validate([
        'start_date' => ['nullable', 'date_format:Y-m-d'],
        'end_date' => [
            'nullable',
            'date_format:Y-m-d',
            'after_or_equal:start_date',
        ],
    ]);

    // 4. Ambil riwayat presensi milik pengguna
    $query = AttendanceRecord::with('workSchedule')
        ->where('user_id', $user->id);

    // 5. Terapkan filter tanggal mulai
    if (!empty($validated['start_date'])) {
        $query->whereDate(
            'attendance_date',
            '>=',
            $validated['start_date']
        );
    }

    // 6. Terapkan filter tanggal akhir
    if (!empty($validated['end_date'])) {
        $query->whereDate(
            'attendance_date',
            '<=',
            $validated['end_date']
        );
    }

    // 7. Ambil data dengan pagination
    $attendance = $query
        ->orderByDesc('attendance_date')
        ->paginate(10);

    // 8. Kirim respons
    return response()->json([
        'message' => 'Riwayat presensi berhasil diambil.',
        'data' => $attendance->items(),
        'pagination' => [
            'current_page' => $attendance->currentPage(),
            'last_page' => $attendance->lastPage(),
            'per_page' => $attendance->perPage(),
            'total' => $attendance->total(),
        ],
    ], 200);
}

}
