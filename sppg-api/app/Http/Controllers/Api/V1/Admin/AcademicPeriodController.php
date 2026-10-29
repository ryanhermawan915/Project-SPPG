<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicPeriodController extends Controller
{
    private function ensureAdmin(Request $request): ?JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya admin yang dapat mengakses fitur ini.',
            ], 403);
        }

        return null;
    }

    public function index(Request $request): JsonResponse
    {
        if ($error = $this->ensureAdmin($request)) {
            return $error;
        }

        $periods = AcademicPeriod::query()
            ->orderByDesc('start_date')
            ->paginate(10);

        return response()->json([
            'message' => 'Daftar periode akademik berhasil diambil.',
            'data' => $periods->items(),
            'pagination' => [
                'current_page' => $periods->currentPage(),
                'last_page' => $periods->lastPage(),
                'per_page' => $periods->perPage(),
                'total' => $periods->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        if ($error = $this->ensureAdmin($request)) {
            return $error;
        }

        $period = AcademicPeriod::find($id);

        if (! $period) {
            return response()->json([
                'message' => 'Periode akademik tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail periode akademik berhasil diambil.',
            'data' => $period,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($error = $this->ensureAdmin($request)) {
            return $error;
        }

        $validated = $request->validate([
            'academic_year' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}\/\d{4}$/',
            ],
            'semester' => [
                'required',
                Rule::in(['odd', 'even']),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $exists = AcademicPeriod::where(
            'academic_year',
            $validated['academic_year']
        )->where('semester', $validated['semester'])->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Periode untuk tahun ajaran dan semester tersebut sudah ada.',
            ], 422);
        }

        $period = AcademicPeriod::create($validated);

        return response()->json([
            'message' => 'Periode akademik berhasil ditambahkan.',
            'data' => $period,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if ($error = $this->ensureAdmin($request)) {
            return $error;
        }

        $period = AcademicPeriod::find($id);

        if (! $period) {
            return response()->json([
                'message' => 'Periode akademik tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'academic_year' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}\/\d{4}$/',
            ],
            'semester' => [
                'sometimes',
                'required',
                Rule::in(['odd', 'even']),
            ],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $academicYear = $validated['academic_year'] ?? $period->academic_year;
        $semester = $validated['semester'] ?? $period->semester;
        $startDate = $validated['start_date'] ?? $period->start_date;
        $endDate = $validated['end_date'] ?? $period->end_date;

        if (strtotime((string) $endDate) <= strtotime((string) $startDate)) {
            return response()->json([
                'message' => 'Tanggal selesai harus setelah tanggal mulai.',
            ], 422);
        }

        $duplicate = AcademicPeriod::where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->where('id', '!=', $period->id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'Periode untuk tahun ajaran dan semester tersebut sudah ada.',
            ], 422);
        }

        $period->update($validated);

        return response()->json([
            'message' => 'Periode akademik berhasil diperbarui.',
            'data' => $period->fresh(),
        ]);
    }

    public function activate(Request $request, int $id): JsonResponse
    {
        if ($error = $this->ensureAdmin($request)) {
            return $error;
        }

        $period = AcademicPeriod::find($id);

        if (! $period) {
            return response()->json([
                'message' => 'Periode akademik tidak ditemukan.',
            ], 404);
        }

        $period->update(['is_active' => true]);

        return response()->json([
            'message' => 'Periode akademik berhasil diaktifkan.',
            'data' => $period->fresh(),
        ]);
    }

    public function deactivate(Request $request, int $id): JsonResponse
    {
        if ($error = $this->ensureAdmin($request)) {
            return $error;
        }

        $period = AcademicPeriod::find($id);

        if (! $period) {
            return response()->json([
                'message' => 'Periode akademik tidak ditemukan.',
            ], 404);
        }

        $period->update(['is_active' => false]);

        return response()->json([
            'message' => 'Periode akademik berhasil dinonaktifkan.',
            'data' => $period->fresh(),
        ]);
    }
}
