// ⚠️ SEMUA DATA DI FILE INI ADALAH DUMMY / PLACEHOLDER UNTUK DEMO UI.
// Ganti dengan fetch dari Laravel saat backend siap.

export const currentAdmin = {
    name: "Admin Demo",
    email: "admin@sppg.demo",
    role: "Administrator",
    };

    // ⚠️ KREDENSIAL DEMO — HANYA untuk demo UI. JANGAN dipakai di production.
    export const demoCredentials = {
    email: "admin@sppg.demo",
    password: "demo1234",
    name: "Admin Demo",
    role: "Administrator",
    };

    export const dashboardStats = {
    totalPegawai: "—",
    hadirHariIni: "—",
    terlambat: "—",
    perluDiproses: "—",
    };

    export const recentActivities = [
    // kosongkan untuk melihat empty state
    // { id: 1, nama: "Guru A (dummy)", jenis: "Presensi Masuk", waktu: "2025-01-01 07:05", status: "Hadir" },
    ];

    export const pendaftarMenunggu = [
    { id: "REG-001", nama: "Pendaftar Dummy 1", email: "dummy1@contoh.test", nomorPegawai: "—", tanggalDaftar: "2025-01-01", status: "Menunggu" },
    { id: "REG-002", nama: "Pendaftar Dummy 2", email: "dummy2@contoh.test", nomorPegawai: "—", tanggalDaftar: "2025-01-02", status: "Menunggu" },
    ];

    export const dataGuru = [
    { id: 1, nama: "Guru Dummy A", nomorPegawai: "—", email: "a@contoh.test", status: "Aktif" },
    { id: 2, nama: "Karyawan Dummy B", nomorPegawai: "—", email: "b@contoh.test", status: "Nonaktif" },
    { id: 3, nama: "Guru Dummy C", nomorPegawai: "—", email: "c@contoh.test", status: "Aktif" },
    ];

    export const pengajuanIzin = [
    { id: "IZN-001", pegawai: "Guru Dummy A", jenis: "Izin", tanggal: "2025-01-05", alasan: "Contoh alasan (dummy)", lampiran: null, status: "Menunggu" },
    ];

    export const koreksiPresensi = [
    { id: "KOR-001", pegawai: "Karyawan Dummy B", tanggal: "2025-01-03", waktuTercatat: "07:45", waktuDiminta: "07:00", alasan: "Contoh alasan koreksi (dummy)", status: "Menunggu" },
    ];

    export const notifikasiSistem = [
    { id: "N-001", judul: "Simulasi sistem", pesan: "Notifikasi contoh (dummy)", dibaca: false },
    ];

    // Jadwal: semua kosong = placeholder, TIDAK mengasumsikan aturan PRD
    export const jadwalDefault = {
    jamMasuk: "",
    batasTerlambat: "",
    jamPulang: "",
    toleransiMenit: "",
    hariKerja: [],
    };