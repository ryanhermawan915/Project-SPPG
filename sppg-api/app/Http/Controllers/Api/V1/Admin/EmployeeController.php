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
}
