<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    /**
     * Memastikan hanya admin yang dapat mengelola jadwal.
     */
    private function ensureAdmin(Request $request): ?JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya admin yang diizinkan.',
            ], 403);
        }

        return null;
    }

    /**
     * Menampilkan semua jadwal kerja.
     */
    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        $schedules = WorkSchedule::query()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(10);

        return response()->json([
            'message' => 'Daftar jadwal kerja berhasil diambil.',
            'data' => $schedules->items(),
            'pagination' => [
                'current_page' => $schedules->currentPage(),
                'last_page' => $schedules->lastPage(),
                'per_page' => $schedules->perPage(),
                'total' => $schedules->total(),
            ],
        ]);
    }

    /**
     * Menampilkan detail satu jadwal.
     */
    public function show(
        Request $request,
        int $id
    ): JsonResponse {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        $schedule = WorkSchedule::find($id);

        if (!$schedule) {
            return response()->json([
                'message' => 'Jadwal kerja tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail jadwal kerja berhasil diambil.',
            'data' => $schedule,
        ]);
    }

    /**
     * Membuat jadwal kerja baru.
     */
    public function store(Request $request): JsonResponse
    {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'day_of_week' => [
                'nullable',
                'integer',
                'between:0,6',
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'late_tolerance_minutes' => [
                'sometimes',
                'integer',
                'between:0,65535',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $schedule = WorkSchedule::create($validated);

        return response()->json([
            'message' => 'Jadwal kerja berhasil ditambahkan.',
            'data' => $schedule->fresh(),
        ], 201);
    }

    /**
     * Memperbarui jadwal kerja.
     */
    public function update(
        Request $request,
        int $id
    ): JsonResponse {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        $schedule = WorkSchedule::find($id);

        if (!$schedule) {
            return response()->json([
                'message' => 'Jadwal kerja tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'day_of_week' => [
                'sometimes',
                'nullable',
                'integer',
                'between:0,6',
            ],
            'start_time' => [
                'sometimes',
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'sometimes',
                'required',
                'date_format:H:i',
            ],
            'late_tolerance_minutes' => [
                'sometimes',
                'integer',
                'between:0,65535',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $startTime = $validated['start_time']
            ?? substr((string) $schedule->start_time, 0, 5);

        $endTime = $validated['end_time']
            ?? substr((string) $schedule->end_time, 0, 5);

        if ($endTime <= $startTime) {
            return response()->json([
                'message' => 'Jam pulang harus setelah jam masuk.',
                'errors' => [
                    'end_time' => [
                        'Jam pulang harus setelah jam masuk.',
                    ],
                ],
            ], 422);
        }

        $schedule->update($validated);

        return response()->json([
            'message' => 'Jadwal kerja berhasil diperbarui.',
            'data' => $schedule->fresh(),
        ]);
    }

    /**
     * Mengaktifkan jadwal.
     */
    public function activate(
        Request $request,
        int $id
    ): JsonResponse {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        $schedule = WorkSchedule::find($id);

        if (!$schedule) {
            return response()->json([
                'message' => 'Jadwal kerja tidak ditemukan.',
            ], 404);
        }

        $schedule->update([
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Jadwal kerja berhasil diaktifkan.',
            'data' => $schedule->fresh(),
        ]);
    }

    /**
     * Menonaktifkan jadwal.
     */
    public function deactivate(
        Request $request,
        int $id
    ): JsonResponse {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        $schedule = WorkSchedule::find($id);

        if (!$schedule) {
            return response()->json([
                'message' => 'Jadwal kerja tidak ditemukan.',
            ], 404);
        }

        $schedule->update([
            'is_active' => false,
        ]);

        return response()->json([
            'message' => 'Jadwal kerja berhasil dinonaktifkan.',
            'data' => $schedule->fresh(),
        ]);
    }
}
