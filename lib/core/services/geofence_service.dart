import 'dart:math';
import 'package:geolocator/geolocator.dart';

class GeofenceResult {
  final bool isInside;
  final double distanceMeters;
  final double? userLat;
  final double? userLng;
  final String? errorMessage;

  GeofenceResult({
    required this.isInside,
    required this.distanceMeters,
    this.userLat,
    this.userLng,
    this.errorMessage,
  });
}

class GeofenceService {
  // Koordinat sekolah (GANTI dengan lokasi sekolah Anda yang sebenarnya)
  // Contoh: Universitas Teknologi Yogyakarta
  static const double schoolLat = -7.7598;
  static const double schoolLng = 110.4080;
  static const double radiusMeters = 200; // Radius 200 meter

  /// Cek apakah user berada dalam radius sekolah
  static Future<GeofenceResult> checkLocation() async {
    try {
      // 1. Cek service lokasi aktif?
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        return GeofenceResult(
          isInside: false,
          distanceMeters: -1,
          errorMessage: 'Layanan lokasi (GPS) tidak aktif. Aktifkan GPS terlebih dahulu.',
        );
      }

      // 2. Cek permission
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          return GeofenceResult(
            isInside: false,
            distanceMeters: -1,
            errorMessage: 'Izin lokasi ditolak. Aplikasi butuh izin lokasi untuk presensi.',
          );
        }
      }

      if (permission == LocationPermission.deniedForever) {
        return GeofenceResult(
          isInside: false,
          distanceMeters: -1,
          errorMessage: 'Izin lokasi ditolak permanen. Buka Settings untuk mengaktifkan.',
        );
      }

      // 3. Ambil posisi user
      final position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      ).timeout(
        const Duration(seconds: 15),
        onTimeout: () => throw Exception('Timeout mendapatkan lokasi'),
      );

      // 4. Hitung jarak ke sekolah
      final distance = Geolocator.distanceBetween(
        position.latitude,
        position.longitude,
        schoolLat,
        schoolLng,
      );

      return GeofenceResult(
        isInside: distance <= radiusMeters,
        distanceMeters: distance,
        userLat: position.latitude,
        userLng: position.longitude,
      );
    } catch (e) {
      return GeofenceResult(
        isInside: false,
        distanceMeters: -1,
        errorMessage: 'Gagal cek lokasi: ${e.toString()}',
      );
    }
  }

  /// Jarak manusiawi: "150 m" atau "1.2 km"
  static String formatDistance(double meters) {
    if (meters < 0) return '-';
    if (meters < 1000) return '${meters.toStringAsFixed(0)} m';
    return '${(meters / 1000).toStringAsFixed(2)} km';
  }
}
