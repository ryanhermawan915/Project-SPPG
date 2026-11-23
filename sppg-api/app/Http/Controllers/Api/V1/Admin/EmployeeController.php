<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaceProfile;
use App\Models\FaceProfileImage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak.',
            ], 403);
        }

        // Validasi data karyawan
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'employee_number' => 'nullable|string|max:50|unique:employee_profiles,employee_number',
            'position' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        // Buat password sementara secara acak
        $temporaryPassword = Str::random(12);

        // Simpan akun dan profil karyawan dalam satu transaksi
        $employee = DB::transaction(function () use ($validated, $temporaryPassword) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $temporaryPassword,
                'role' => 'employee',
                'account_status' => 'active',
                'must_change_password' => true,
                'password_changed_at' => null,
            ]);

            $user->employeeProfile()->create([
                'employee_number' => $validated['employee_number'] ?? null,
                'position' => $validated['position'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            return $user;
        });

        return response()->json([
            'message' => 'Akun karyawan berhasil dibuat.',
            'data' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'role' => $employee->role,
                'account_status' => $employee->account_status,
                'must_change_password' => $employee->must_change_password,
                'temporary_password' => $temporaryPassword,
            ],
        ], 201);
    }

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

        $employee = User::with([
            'employeeProfile',
            'faceProfile.images',
        ])
            ->where('role', 'employee')
            ->find($userId);

        if (! $employee) {
            return response()->json([
                'message' => 'Data pegawai tidak ditemukan.',
            ], 404);
        }

        $profile = $employee->employeeProfile;
        $faceProfile = $employee->faceProfile;

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

                'face_profile' => $faceProfile ? [
                    'id' => $faceProfile->id,
                    'is_active' => $faceProfile->is_active,
                    'model_version' => $faceProfile->model_version,
                    'registered_at' => $faceProfile->registered_at,
                    'total_images' => $faceProfile->images->count(),
                    'images' => $faceProfile->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'angle' => $image->angle,
                            'created_at' => $image->created_at,
                        ];
                    })->values(),
                ] : null,
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
                'nullable',
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

    public function uploadFaceImages(Request $request, int $userId): JsonResponse
    {
        // Pastikan hanya admin yang dapat mengunggah foto.
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya admin yang diizinkan.',
            ], 403);
        }

        // Cari akun karyawan.
        $employee = User::where('role', 'employee')->find($userId);

        if (! $employee) {
            return response()->json([
                'message' => 'Data pegawai tidak ditemukan.',
            ], 404);
        }

        // Validasi foto yang diunggah.
        $validated = $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'angles' => 'sometimes|array',
            'angles.*' => 'nullable|string|max:30',
        ]);

        $storedPaths = [];

        try {
            DB::transaction(function () use (
                $employee,
                $request,
                &$storedPaths
            ) {
                // Buat profil wajah jika belum tersedia.
                $faceProfile = $employee->faceProfile()->firstOrCreate(
                    [],
                    [
                        'is_active' => false,
                    ]
                );

                foreach ($request->file('images') as $index => $image) {

                    // Simpan foto di private storage.
                    $path = $image->store(
                        "face-profiles/{$employee->id}",
                        'private'
                    );

                    if (! $path) {
                        throw new \RuntimeException(
                            'Gagal menyimpan foto wajah.'
                        );
                    }

                    $storedPaths[] = $path;

                    // Simpan informasi foto ke database.
                    $faceProfile->images()->create([
                        'image_path' => $path,
                        'angle' => $request->input("angles.{$index}") ?: 'other',
                    ]);
                }
            });
        } catch (\Throwable $exception) {

            // Hapus file jika proses database gagal.
            foreach ($storedPaths as $path) {
                Storage::disk('private')->delete($path);
            }

            throw $exception;
        }

        $faceProfile = $employee->faceProfile()->with('images')->first();

        return response()->json([
            'message' => 'Foto wajah berhasil diunggah.',
            'data' => [
                'employee_id' => $employee->id,
                'face_profile_id' => $faceProfile->id,
                'total_images' => $faceProfile->images->count(),
                'images' => $faceProfile->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'angle' => $image->angle,
                        'created_at' => $image->created_at,
                    ];
                }),
            ],
        ], 201);
    }

    public function showFaceImage(
        Request $request,
        int $userId,
        int $imageId
    ) {
        // Hanya admin yang boleh melihat foto wajah.
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya admin yang diizinkan.',
            ], 403);
        }

        // Cari pegawai berdasarkan ID.
        $employee = User::where('role', 'employee')->find($userId);

        if (! $employee) {
            return response()->json([
                'message' => 'Data pegawai tidak ditemukan.',
            ], 404);
        }

        // Ambil foto yang benar-benar dimiliki pegawai tersebut.
        $faceImage = $employee->faceProfile
            ? $employee->faceProfile->images()->find($imageId)
            : null;

        if (! $faceImage) {
            return response()->json([
                'message' => 'Foto wajah tidak ditemukan.',
            ], 404);
        }

        $imagePath = $faceImage->image_path;
        $disk = Storage::disk('private');

        // Pastikan file tersedia di private storage.
        if (! $disk->exists($imagePath)) {
            return response()->json([
                'message' => 'File foto wajah tidak ditemukan.',
            ], 404);
        }
       
        // Ambil tipe file gambar.
        $mimeType = $disk->mimeType($imagePath);

        // Ambil ukuran file.
        $fileSize = $disk->size($imagePath);

        // Kirim gambar sebagai respons stream.
        return response()->stream(
            function () use ($disk, $imagePath) {
                $stream = $disk->readStream($imagePath);

                if ($stream === false) {
                    return;
                }

                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Length' => $fileSize,
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function deleteFaceImage(
        Request $request,
        int $userId,
        int $imageId
    ): JsonResponse {
        // Hanya admin yang boleh menghapus foto wajah.
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

        $faceProfile = $employee->faceProfile;

        if (! $faceProfile) {
            return response()->json([
                'message' => 'Profil wajah pegawai tidak ditemukan.',
            ], 404);
        }

        $faceImage = $faceProfile->images()->find($imageId);

        if (! $faceImage) {
            return response()->json([
                'message' => 'Foto wajah tidak ditemukan.',
            ], 404);
        }

        $imagePath = $faceImage->image_path;

        // Hapus data foto dan perbarui profil dalam transaksi database.
        $result = DB::transaction(function () use ($faceImage, $faceProfile) {
            $faceImage->delete();

            $totalImages = $faceProfile->images()->count();

            // Nonaktifkan dan reset profil jika tidak ada foto tersisa.
            if ($totalImages === 0) {
                $faceProfile->update([
                    'is_active' => false,
                    'reference_path' => null,
                    'model_version' => null,
                    'registered_at' => null,
                ]);
            }

            return [
                'total_images' => $totalImages,
                'is_active' => $faceProfile->fresh()->is_active,
            ];
        });

        // Hapus file fisik setelah transaksi database berhasil.
        $fileCleanupPending = false;

        if (Storage::disk('private')->exists($imagePath)) {
            if (! Storage::disk('private')->delete($imagePath)) {
                $fileCleanupPending = true;

                Log::warning('Gagal menghapus file foto wajah.', [
                    'employee_id' => $employee->id,
                    'image_id' => $imageId,
                    'image_path' => $imagePath,
                ]);
            }
        }

        return response()->json([
            'message' => 'Foto wajah berhasil dihapus.',
            'data' => [
                'employee_id' => $employee->id,
                'face_profile_id' => $faceProfile->id,
                'deleted_image_id' => $imageId,
                'total_images' => $result['total_images'],
                'is_active' => $result['is_active'],
                'file_cleanup_pending' => $fileCleanupPending,
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
