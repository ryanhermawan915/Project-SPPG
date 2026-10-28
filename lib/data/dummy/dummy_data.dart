import '../models/user_model.dart';
import '../models/attendance_model.dart';
import '../models/message_model.dart';

class DummyData {
  DummyData._();

  /// User default (sesuai desain Figma: Gibran Raka S.pd)
  static final UserModel currentUser = UserModel(
    nip: '198705152024011001',
    nama: 'Gibran Raka S.pd',
    email: 'GibranRaka2@gmail.com',
    noHp: '0897657743568',
    jenisKelamin: 'Laki - Laki',
    mataPelajaran: 'Olahraga',
    isFaceRegistered: false,
  );

  /// Daftar hadir 6 bulan terakhir (untuk grafik)
  /// Persentase: Jan 100, Feb 50, Mar 100, Apr 90, Mei 80, Jun 60
  static final List<int> chartData = [100, 50, 100, 90, 80, 60];

  /// Riwayat presensi (7 hari terakhir)
  static final List<AttendanceModel> attendanceHistory = [
    AttendanceModel(
      id: 'att-001',
      nip: currentUser.nip,
      tanggal: DateTime(2026, 10, 6),
      jamMasuk: DateTime(2026, 10, 6, 6, 54, 38),
      jamPulang: DateTime(2026, 10, 6, 15, 2, 0),
      status: AttendanceStatus.hadir,
    ),
    AttendanceModel(
      id: 'att-002',
      nip: currentUser.nip,
      tanggal: DateTime(2026, 10, 5),
      jamMasuk: DateTime(2026, 10, 5, 7, 20, 0),
      jamPulang: DateTime(2026, 10, 5, 15, 5, 0),
      status: AttendanceStatus.terlambat,
    ),
    AttendanceModel(
      id: 'att-003',
      nip: currentUser.nip,
      tanggal: DateTime(2026, 10, 4),
      jamMasuk: DateTime(2026, 10, 4, 6, 50, 0),
      jamPulang: DateTime(2026, 10, 4, 15, 0, 0),
      status: AttendanceStatus.hadir,
    ),
    AttendanceModel(
      id: 'att-004',
      nip: currentUser.nip,
      tanggal: DateTime(2026, 10, 3),
      status: AttendanceStatus.izin,
    ),
    AttendanceModel(
      id: 'att-005',
      nip: currentUser.nip,
      tanggal: DateTime(2026, 10, 2),
      status: AttendanceStatus.sakit,
    ),
    AttendanceModel(
      id: 'att-006',
      nip: currentUser.nip,
      tanggal: DateTime(2026, 10, 1),
      jamMasuk: DateTime(2026, 10, 1, 6, 55, 0),
      jamPulang: DateTime(2026, 10, 1, 15, 3, 0),
      status: AttendanceStatus.hadir,
    ),
    AttendanceModel(
      id: 'att-007',
      nip: currentUser.nip,
      tanggal: DateTime(2026, 9, 30),
      status: AttendanceStatus.alpha,
    ),
  ];

  /// Pesan (izin/sakit) yang pernah dikirim
  static final List<MessageModel> messages = [
    MessageModel(
      id: 'msg-001',
      nip: currentUser.nip,
      type: MessageType.izin,
      subjek: 'Izin Keperluan Keluarga',
      keterangan: 'Saya izin tidak masuk karena ada keperluan keluarga di luar kota.',
      tanggalKirim: DateTime(2026, 10, 3, 8, 30),
    ),
    MessageModel(
      id: 'msg-002',
      nip: currentUser.nip,
      type: MessageType.sakit,
      subjek: 'Sakit Demam',
      keterangan: 'Saya sakit demam dan disarankan istirahat oleh dokter.',
      tanggalKirim: DateTime(2026, 10, 2, 6, 45),
    ),
  ];
}