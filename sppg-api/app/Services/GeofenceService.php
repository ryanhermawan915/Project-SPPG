<?php

namespace App\Services;

use App\Models\SchoolLocation;

class GeofenceService
{
    public function check(float $latitude, float $longitude): array
    {
        // Ambil lokasi sekolah yang aktif
        $location = SchoolLocation::where('is_active', true)->first();

        // Jika lokasi sekolah belum tersedia
        if (!$location) {
            return [
                'is_inside' => false,
                'distance_meters' => null,
                'radius_meters' => null,
                'message' => 'Lokasi sekolah belum dikonfigurasi atau tidak aktif.',
            ];
        }

        // Koordinat lokasi sekolah
        $schoolLatitude = (float) $location->latitude;
        $schoolLongitude = (float) $location->longitude;

        // Konversi koordinat ke radian
        $lat1 = deg2rad($schoolLatitude);
        $lon1 = deg2rad($schoolLongitude);
        $lat2 = deg2rad($latitude);
        $lon2 = deg2rad($longitude);

        // Selisih koordinat
        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;

        // Rumus Haversine
        $a = sin($deltaLat / 2) ** 2
            + cos($lat1) * cos($lat2)
            * sin($deltaLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Radius rata-rata bumi dalam meter
        $earthRadius = 6371000;

        // Hasil perhitungan jarak
        $distance = $earthRadius * $c;

        // Periksa apakah pegawai berada dalam radius
        $isInside = $distance <= $location->radius_meters;

        return [
            'is_inside' => $isInside,
            'distance_meters' => round($distance, 2),
            'radius_meters' => $location->radius_meters,
            'message' => $isInside
                ? 'Lokasi berada dalam area presensi.'
                : 'Anda berada di luar area presensi.',
        ];
    }
}
