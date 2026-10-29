<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Pastikan hanya admin yang dapat mengakses endpoint ini.
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya admin yang diizinkan.',
            ], 403);
        }

        $employees = User::with('employeeProfile')
            ->where('role', 'employee')
            ->orderBy('name')
            ->paginate(10);

        return response()->json([
            'message' => 'Daftar pegawai berhasil diambil.',
            'data' => $employees->getCollection()->map(function ($employee) {
                $profile = $employee->employeeProfile;

                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'account_status' => $employee->account_status,
                    'employee_number' => $profile?->employee_number,
                    'position' => $profile?->position,
                    'phone' => $profile?->phone,
                    'address' => $profile?->address,
                    'created_at' => $employee->created_at,
                ];
            }),
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ],
        ]);
    }

    public function show(Request $request, int $userId): JsonResponse
    {
        // Hanya admin yang boleh mengakses detail pegawai.
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya admin yang diizinkan.',
            ], 403);
        }

        $employee = User::with('employeeProfile')
            ->where('role', 'employee')
            ->find($userId);

        if (! $employee) {
            return response()->json([
                'message' => 'Data pegawai tidak ditemukan.',
            ], 404);
        }

        $profile = $employee->employeeProfile;

        return response()->json([
            'message' => 'Detail pegawai berhasil diambil.',
            'data' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'role' => $employee->role,
                'account_status' => $employee->account_status,
                'employee_number' => $profile?->employee_number,
                'position' => $profile?->position,
                'phone' => $profile?->phone,
                'address' => $profile?->address,
                'approved_by' => $employee->approved_by,
                'approved_at' => $employee->approved_at,
                'created_at' => $employee->created_at,
            ],
        ]);
    }

    
    public function update(Request $request, int $userId): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya admin yang diizinkan.',
            ], 403);
        }

        $employee = User::with('employeeProfile')
            ->where('role', 'employee')
            ->find($userId);

        if (! $employee) {
            return response()->json([
                'message' => 'Data pegawai tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')
                    ->ignore($employee->id),
            ],
            'employee_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique(
                    'employee_profiles',
                    'employee_number'
                )->ignore($employee->employeeProfile?->id),
            ],
            'position' => 'sometimes|nullable|string|max:100',
            'phone' => 'sometimes|nullable|string|max:30',
            'address' => 'sometimes|nullable|string|max:1000',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $employee,
            $validated
        ) {
            if (array_key_exists('name', $validated)) {
                $employee->name = $validated['name'];
            }

            if (array_key_exists('email', $validated)) {
                $employee->email = strtolower($validated['email']);
            }

            $employee->save();

            $profileData = array_intersect_key($validated, array_flip([
                'employee_number',
                'position',
                'phone',
                'address',
            ]));

            if (! empty($profileData)) {
                $employee->employeeProfile()->updateOrCreate(
                    ['user_id' => $employee->id],
                    $profileData
                );
            }
        });

        $employee->refresh()->load('employeeProfile');
        $profile = $employee->employeeProfile;

        return response()->json([
            'message' => 'Data pegawai berhasil diperbarui.',
            'data' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'role' => $employee->role,
                'account_status' => $employee->account_status,
                'employee_number' => $profile?->employee_number,
                'position' => $profile?->position,
                'phone' => $profile?->phone,
                'address' => $profile?->address,
            ],
        ]);
    }

    public function deactivate(Request $request, int $userId): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya admin yang diizinkan.',
            ], 403);
        }

        $employee = User::where('role', 'employee')->find($userId);

        if (! $employee) {
            return response()->json([
                'message' => 'Data pegawai tidak ditemukan.',
            ], 404);
        }

        if ($employee->account_status !== 'active') {
            return response()->json([
                'message' => 'Hanya akun aktif yang dapat dinonaktifkan.',
            ], 422);
        }

        $employee->update([
            'account_status' => 'inactive',
        ]);

        // Cabut token agar sesi login yang sudah ada tidak tetap aktif.
        $employee->tokens()->delete();

        return response()->json([
            'message' => 'Akun pegawai berhasil dinonaktifkan.',
            'data' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'account_status' => $employee->account_status,
            ],
        ]);
    }

    public function activate(Request $request, int $userId): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya admin yang diizinkan.',
            ], 403);
        }

        $employee = User::where('role', 'employee')->find($userId);

        if (! $employee) {
            return response()->json([
                'message' => 'Data pegawai tidak ditemukan.',
            ], 404);
        }

        if ($employee->account_status !== 'inactive') {
            return response()->json([
                'message' => 'Hanya akun nonaktif yang dapat diaktifkan kembali.',
            ], 422);
        }

        $employee->update([
            'account_status' => 'active',
        ]);

        return response()->json([
            'message' => 'Akun pegawai berhasil diaktifkan kembali.',
            'data' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'account_status' => $employee->account_status,
            ],
        ]);
    }


}
