<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceEvent;
use App\Models\AttendanceStatusChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{


public function index(Request $request)
{
    $admin = $request->user();

    if ($admin->role !== 'admin' || $admin->account_status !== 'active') {
        return response()->json([
            'message' => 'Anda tidak memiliki akses untuk melihat data presensi.',
        ], 403);
    }

    $validated = $request->validate([
        'date' => ['sometimes', 'date_format:Y-m-d'],
        'start_date' => ['sometimes', 'date_format:Y-m-d'],
        'end_date' => [
            'sometimes',
            'date_format:Y-m-d',
            'after_or_equal:start_date',
        ],
        'status' => [
            'sometimes',
            Rule::in(['present', 'late', 'absent', 'permission', 'sick']),
        ],
        'search' => ['sometimes', 'string', 'max:100'],
        'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
    ]);

    $query = AttendanceRecord::with([
        'user.employeeProfile',
        'workSchedule',
    ]);

    if (isset($validated['date'])) {
        $query->whereDate('attendance_date', $validated['date']);
    }

    if (isset($validated['start_date'])) {
        $query->whereDate('attendance_date', '>=', $validated['start_date']);
    }

    if (isset($validated['end_date'])) {
        $query->whereDate('attendance_date', '<=', $validated['end_date']);
    }

    if (isset($validated['status'])) {
        $query->where('status', $validated['status']);
    }

    if (isset($validated['search'])) {
        $search = $validated['search'];

        $query->whereHas('user', function ($userQuery) use ($search) {
            $userQuery->where('name', 'like', "%{$search}%")
                ->orWhereHas('employeeProfile', function ($profileQuery) use ($search) {
                    $profileQuery->where('employee_number', 'like', "%{$search}%");
                });
        });
    }

    $perPage = $validated['per_page'] ?? 10;

    $attendances = $query
        ->orderByDesc('attendance_date')
        ->orderByDesc('created_at')
        ->paginate($perPage);

    $attendances->getCollection()->transform(function ($attendance) {
        return [
            'id' => $attendance->id,
            'attendance_date' => $attendance->attendance_date->format('Y-m-d'),
            'check_in_time' => $attendance->check_in_time,
            'check_out_time' => $attendance->check_out_time,
            'status' => $attendance->status,
            'notes' => $attendance->notes,

            'employee' => [
                'id' => $attendance->user->id,
                'name' => $attendance->user->name,
                'email' => $attendance->user->email,
                'employee_number' => $attendance->user->employeeProfile?->employee_number,
                'position' => $attendance->user->employeeProfile?->position,
            ],

            'work_schedule' => $attendance->workSchedule ? [
                'id' => $attendance->workSchedule->id,
                'name' => $attendance->workSchedule->name,
                'start_time' => $attendance->workSchedule->start_time,
                'end_time' => $attendance->workSchedule->end_time,
                'late_tolerance_minutes' => $attendance->workSchedule->late_tolerance_minutes,
            ] : null,
        ];
    });

    return response()->json([
        'message' => 'Daftar presensi berhasil diambil.',
        'data' => $attendances->items(),
        'pagination' => [
            'current_page' => $attendances->currentPage(),
            'last_page' => $attendances->lastPage(),
            'per_page' => $attendances->perPage(),
            'total' => $attendances->total(),
        ],
    ], 200);
}

public function show(Request $request, $id)
{
    $admin = $request->user();

    if ($admin->role !== 'admin' || $admin->account_status !== 'active') {
        return response()->json([
            'message' => 'Anda tidak memiliki akses untuk melihat detail presensi.',
        ], 403);
    }

    $attendance = AttendanceRecord::with([
        'user.employeeProfile',
        'workSchedule',
        'attendanceEvents',
        'statusChanges.admin',
    ])->find($id);

    if (!$attendance) {
        return response()->json([
            'message' => 'Data presensi tidak ditemukan.',
        ], 404);
    }

    return response()->json([
        'message' => 'Detail presensi berhasil diambil.',
        'data' => [
            'id' => $attendance->id,
            'attendance_date' => $attendance->attendance_date->format('Y-m-d'),
            'check_in_time' => $attendance->check_in_time,
            'check_out_time' => $attendance->check_out_time,
            'status' => $attendance->status,
            'notes' => $attendance->notes,

            'employee' => [
                'id' => $attendance->user->id,
                'name' => $attendance->user->name,
                'email' => $attendance->user->email,
                'employee_number' => $attendance->user->employeeProfile?->employee_number,
                'position' => $attendance->user->employeeProfile?->position,
            ],

            'work_schedule' => $attendance->workSchedule ? [
                'id' => $attendance->workSchedule->id,
                'name' => $attendance->workSchedule->name,
                'start_time' => $attendance->workSchedule->start_time,
                'end_time' => $attendance->workSchedule->end_time,
                'late_tolerance_minutes' => $attendance->workSchedule->late_tolerance_minutes,
            ] : null,

            'attendance_events' => $attendance->attendanceEvents->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'recorded_at' => $event->recorded_at,
                    'latitude' => $event->latitude,
                    'longitude' => $event->longitude,
                    'verification_method' => $event->verification_method,
                    'verification_status' => $event->verification_status,
                    'notes' => $event->notes,
                ];
            }),

            'status_changes' => $attendance->statusChanges->map(function ($change) {
                return [
                    'id' => $change->id,
                    'old_status' => $change->old_status,
                    'new_status' => $change->new_status,
                    'reason' => $change->reason,
                    'changed_at' => $change->created_at,
                    'admin' => [
                        'id' => $change->admin->id,
                        'name' => $change->admin->name,
                    ],
                ];
            }),
        ],
    ], 200);
}

public function updateStatus(Request $request, $id)
{
    $admin = $request->user();

    if ($admin->role !== 'admin' || $admin->account_status !== 'active') {
        return response()->json([
            'message' => 'Anda tidak memiliki akses untuk mengubah status presensi.',
        ], 403);
    }

    $validated = $request->validate([
        'status' => [
            'required',
            Rule::in(['present', 'late', 'absent', 'permission', 'sick']),
        ],
        'reason' => ['required', 'string', 'max:1000'],
    ]);

    $result = DB::transaction(function () use ($id, $admin, $validated) {
        $attendance = AttendanceRecord::whereKey($id)
            ->lockForUpdate()
            ->first();

        if (!$attendance) {
            return null;
        }

        $oldStatus = $attendance->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return [
                'unchanged' => true,
            ];
        }

        AttendanceStatusChange::create([
            'attendance_record_id' => $attendance->id,
            'admin_id' => $admin->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $validated['reason'],
        ]);

        $attendance->update([
            'status' => $newStatus,
        ]);

        return [
            'unchanged' => false,
            'old_status' => $oldStatus,
            'attendance' => $attendance->fresh(),
        ];
    });

    if ($result === null) {
        return response()->json([
            'message' => 'Data presensi tidak ditemukan.',
        ], 404);
    }

    if ($result['unchanged']) {
        return response()->json([
            'message' => 'Status presensi tidak berubah karena status yang dipilih sama dengan status saat ini.',
        ], 422);
    }

    return response()->json([
        'message' => 'Status presensi berhasil diperbarui.',
        'data' => [
            'id' => $result['attendance']->id,
            'attendance_date' => $result['attendance']->attendance_date->format('Y-m-d'),
            'old_status' => $result['old_status'],
            'new_status' => $result['attendance']->status,
            'reason' => $validated['reason'],
        ],
    ], 200);
}

}
