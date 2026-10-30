<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolLocation;
use Illuminate\Http\Request;

class SchoolLocationController extends Controller
{
    private function ensureAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Akses hanya untuk admin.',
            ], 403);
        }

        return null;
    }

    // Melihat konfigurasi lokasi sekolah
    public function show(Request $request)
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $location = SchoolLocation::first();

        if (!$location) {
            return response()->json([
                'message' => 'Lokasi sekolah belum dikonfigurasi.',
            ], 404);
        }

        return response()->json([
            'message' => 'Data lokasi sekolah berhasil diambil.',
            'data' => $location,
        ]);
    }

    // Memperbarui konfigurasi lokasi sekolah
    public function update(Request $request)
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:1'],
        ]);

        $location = SchoolLocation::first();

        if (!$location) {
            $location = SchoolLocation::create($validated);
        } else {
            $location->update($validated);
        }

        return response()->json([
            'message' => 'Konfigurasi lokasi sekolah berhasil diperbarui.',
            'data' => $location,
        ]);
    }
}
