enum AttendanceStatus { hadir, terlambat, izin, sakit, alpha }

extension AttendanceStatusExtension on AttendanceStatus {
  String get label {
    switch (this) {
      case AttendanceStatus.hadir:
        return 'Hadir';
      case AttendanceStatus.terlambat:
        return 'Terlambat';
      case AttendanceStatus.izin:
        return 'Izin';
      case AttendanceStatus.sakit:
        return 'Sakit';
      case AttendanceStatus.alpha:
        return 'Alpha';
    }
  }
}

class AttendanceModel {
  final String id;
  final String nip;
  final DateTime tanggal;
  final DateTime? jamMasuk;
  final DateTime? jamPulang;
  final AttendanceStatus status;

  AttendanceModel({
    required this.id,
    required this.nip,
    required this.tanggal,
    this.jamMasuk,
    this.jamPulang,
    required this.status,
  });

  /// Format jam: HH:mm
  String get jamMasukFormatted =>
      jamMasuk != null ? _formatTime(jamMasuk!) : '-';
  String get jamPulangFormatted =>
      jamPulang != null ? _formatTime(jamPulang!) : '-';

  String _formatTime(DateTime dt) {
    final h = dt.hour.toString().padLeft(2, '0');
    final m = dt.minute.toString().padLeft(2, '0');
    return '$h:$m';
  }

  /// Format tanggal: dd MMM yyyy (contoh: 01 Okt 2026)
  String get tanggalFormatted {
    const bulan = [
      'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
      'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    return '${tanggal.day.toString().padLeft(2, '0')} '
        '${bulan[tanggal.month - 1]} ${tanggal.year}';
  }
}