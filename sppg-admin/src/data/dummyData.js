// Semua data untuk simulasi — nanti tinggal ganti dengan fetch ke API.

// ============ PEGAWAI ============
// status: 'aktif' | 'pending' | 'nonaktif'
export const initialGuru = [
  {
    id: 1, nama: 'Prabowo S.Pd', nip: '112233445',
    jabatan: 'Guru Matematika', email: 'prabowo@sekolah.sch.id',
    telepon: '081234567890', jenisKelamin: 'Laki-laki',
    mapel: 'Matematika', status: 'aktif',
  },
  {
    id: 2, nama: 'Gibran Raka S.Pd', nip: '198705152024011001',
    jabatan: 'Guru Olahraga', email: 'gibran@sekolah.sch.id',
    telepon: '087657743568', jenisKelamin: 'Laki-laki',
    mapel: 'Olahraga', status: 'aktif',
  },
  {
    id: 3, nama: 'Rocky S.Pd', nip: '223344556',
    jabatan: 'Guru Bahasa Inggris', email: 'rocky@sekolah.sch.id',
    telepon: '081298765432', jenisKelamin: 'Laki-laki',
    mapel: 'Bahasa Inggris', status: 'aktif',
  },
  {
    id: 4, nama: 'Purbaya S.Pd', nip: '334455667',
    jabatan: 'Guru IPA', email: 'purbaya@sekolah.sch.id',
    telepon: '081377788899', jenisKelamin: 'Laki-laki',
    mapel: 'IPA', status: 'pending',
  },
  {
    id: 5, nama: 'Teddy S.Pd', nip: '445566778',
    jabatan: 'Guru IPS', email: 'teddy@sekolah.sch.id',
    telepon: '081455566677', jenisKelamin: 'Laki-laki',
    mapel: 'IPS', status: 'aktif',
  },
  {
    id: 6, nama: 'Dedi M S.Pd', nip: '556677889',
    jabatan: 'Guru Seni', email: 'dedi@sekolah.sch.id',
    telepon: '081566677788', jenisKelamin: 'Laki-laki',
    mapel: 'Seni Budaya', status: 'pending',
  },
  {
    id: 7, nama: 'Siti Aminah S.Pd', nip: '667788990',
    jabatan: 'Guru Bahasa Indonesia', email: 'siti@sekolah.sch.id',
    telepon: '081677788899', jenisKelamin: 'Perempuan',
    mapel: 'Bahasa Indonesia', status: 'aktif',
  },
];

// ============ PRESENSI ============
// status: 'Hadir' | 'Terlambat' | 'Izin' | 'Sakit' | 'Alpha'
export const initialPresensi = [
  { id: 1, nama: 'Prabowo S.Pd',    nip: '112233445',          tanggal: '2026-10-06', jamMasuk: '06:54', jamPulang: '14:05', status: 'Hadir' },
  { id: 2, nama: 'Gibran Raka S.Pd', nip: '198705152024011001', tanggal: '2026-10-06', jamMasuk: '07:12', jamPulang: '14:10', status: 'Terlambat' },
  { id: 3, nama: 'Rocky S.Pd',      nip: '223344556',          tanggal: '2026-10-06', jamMasuk: '-',     jamPulang: '-',     status: 'Izin' },
  { id: 4, nama: 'Purbaya S.Pd',    nip: '334455667',          tanggal: '2026-10-06', jamMasuk: '-',     jamPulang: '-',     status: 'Sakit' },
  { id: 5, nama: 'Teddy S.Pd',      nip: '445566778',          tanggal: '2026-10-06', jamMasuk: '-',     jamPulang: '-',     status: 'Alpha' },
  { id: 6, nama: 'Dedi M S.Pd',     nip: '556677889',          tanggal: '2026-10-06', jamMasuk: '07:20', jamPulang: '14:00', status: 'Terlambat' },
  { id: 7, nama: 'Siti Aminah S.Pd', nip: '667788990',         tanggal: '2026-10-05', jamMasuk: '06:50', jamPulang: '14:00', status: 'Hadir' },
  { id: 8, nama: 'Prabowo S.Pd',    nip: '112233445',          tanggal: '2026-10-05', jamMasuk: '06:55', jamPulang: '14:02', status: 'Hadir' },
];

// ============ PENGAJUAN IZIN / SAKIT ============
// status: 'Menunggu' | 'Disetujui' | 'Ditolak'
export const initialPengajuan = [
  {
    id: 1, nama: 'Rocky S.Pd', nip: '223344556', jenis: 'Izin',
    tanggalMulai: '2026-10-06', tanggalSelesai: '2026-10-07',
    alasan: 'Menghadiri acara keluarga di luar kota.',
    tanggalPengajuan: '2026-10-05', status: 'Menunggu',
    lampiran: null, catatanAdmin: '',
  },
  {
    id: 2, nama: 'Purbaya S.Pd', nip: '334455667', jenis: 'Sakit',
    tanggalMulai: '2026-10-06', tanggalSelesai: '2026-10-06',
    alasan: 'Demam dan sakit kepala, disarankan istirahat oleh dokter.',
    tanggalPengajuan: '2026-10-06', status: 'Menunggu',
    lampiran: 'surat_dokter.pdf', catatanAdmin: '',
  },
  {
    id: 3, nama: 'Teddy S.Pd', nip: '445566778', jenis: 'Izin',
    tanggalMulai: '2026-10-03', tanggalSelesai: '2026-10-03',
    alasan: 'Keperluan administrasi bank.',
    tanggalPengajuan: '2026-10-02', status: 'Disetujui',
    lampiran: null, catatanAdmin: 'Disetujui, harap lengkapi bukti jika diminta.',
  },
];

// ============ JADWAL KERJA ============
export const initialJadwal = [
  { id: 1, nama: 'Jadwal Reguler Senin',  hari: 'Senin', jamMasuk: '07:00', jamPulang: '14:00', toleransi: 15, aktif: true },
  { id: 2, nama: 'Jadwal Reguler Selasa', hari: 'Selasa', jamMasuk: '07:00', jamPulang: '14:00', toleransi: 15, aktif: true },
  { id: 3, nama: 'Jadwal Reguler Rabu',   hari: 'Rabu',   jamMasuk: '07:00', jamPulang: '14:00', toleransi: 15, aktif: true },
  { id: 4, nama: 'Jadwal Reguler Kamis',  hari: 'Kamis',  jamMasuk: '07:00', jamPulang: '14:00', toleransi: 15, aktif: true },
  { id: 5, nama: 'Jadwal Reguler Jumat',  hari: 'Jumat',  jamMasuk: '07:00', jamPulang: '11:30', toleransi: 15, aktif: true },
];