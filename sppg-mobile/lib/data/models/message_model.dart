enum MessageType { izin, sakit }

class MessageModel {
  final String id;
  final String nip;
  final MessageType type;
  final String subjek;
  final String keterangan;
  final DateTime tanggalKirim;
  final String? lampiranPath;

  MessageModel({
    required this.id,
    required this.nip,
    required this.type,
    required this.subjek,
    required this.keterangan,
    required this.tanggalKirim,
    this.lampiranPath,
  });

  String get typeLabel => type == MessageType.izin ? 'Izin' : 'Sakit';

  String get tanggalFormatted {
    const bulan = [
      'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
      'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    return '${tanggalKirim.day.toString().padLeft(2, '0')} '
        '${bulan[tanggalKirim.month - 1]} ${tanggalKirim.year}';
  }
}