<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Registrasi akun guru atau karyawan.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',

            'employee_number' =>
                'required|string|max:50|unique:employee_profiles,employee_number',

            'position' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:1000',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
            ]);

            // Registrasi publik hanya boleh membuat akun pegawai.
            $user->role = 'employee';
            $user->account_status = 'pending';
            $user->save();

            // Membuat profil sekaligus mengisi user_id melalui relasi.
            $user->employeeProfile()->create([
                'employee_number' => $validated['employee_number'],
                'position' => $validated['position'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            return $user;
        });

        return response()->json([
            'message' =>
                'Registrasi berhasil. Akun menunggu persetujuan admin.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'account_status' => $user->account_status,
            ],
        ], 201);
    }

    /**
     * Login pegawai atau admin yang sudah aktif.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check(
            $validated['password'],
            $user->password
        )) {
            return response()->json([
                'message' => 'Email atau password tidak sesuai.',
            ], 401);
        }

        if ($user->account_status !== 'active') {
            $message = match ($user->account_status) {
                'pending' => 'Akun masih menunggu persetujuan admin.',
                'rejected' => 'Pendaftaran akun ditolak oleh admin.',
                'inactive' => 'Akun sedang dinonaktifkan.',
                default => 'Akun tidak dapat digunakan.',
            };

            return response()->json([
                'message' => $message,
                'account_status' => $user->account_status,
            ], 403);
        }

        $token = $user->createToken(
            $validated['device_name'] ?? 'sppg-mobile'
        )->plainTextToken;

        $profile = $user->employeeProfile;

        return response()->json([
            'message' => 'Login berhasil.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'account_status' => $user->account_status,
                'employee_profile' => $profile ? [
                    'employee_number' => $profile->employee_number,
                    'position' => $profile->position,
                    'phone' => $profile->phone,
                    'address' => $profile->address,
                ] : null,
            ],
        ]);
    }

    /**
     * Mengambil data pengguna yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->employeeProfile;

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'account_status' => $user->account_status,
                'employee_profile' => $profile ? [
                    'employee_number' => $profile->employee_number,
                    'position' => $profile->position,
                    'phone' => $profile->phone,
                    'address' => $profile->address,
                ] : null,
            ],
        ]);
    }

    /**
     * Menghapus token yang digunakan saat logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
