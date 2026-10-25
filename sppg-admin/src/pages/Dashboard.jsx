import { useNavigate } from 'react-router-dom';
import { useMemo } from 'react';
import { Users, UserCheck, Clock, Mail, TrendingUp, ArrowRight } from 'lucide-react';
import Card from '../components/Card';
import Badge from '../components/Badge';
import { initialGuru, initialPresensi, initialPengajuan } from '../data/dummyData';

export default function Dashboard() {
  const navigate = useNavigate();

  const stats = useMemo(() => {
    const totalPegawai = initialGuru.length;
    const akunAktif = initialGuru.filter((g) => g.status === 'aktif').length;
    const pendingApproval = initialGuru.filter((g) => g.status === 'pending').length;
    const pengajuanMenunggu = initialPengajuan.filter((p) => p.status === 'Menunggu').length;

    const hariIni = '2026-10-06';
    const presensiHariIni = initialPresensi.filter((p) => p.tanggal === hariIni);
    const hadir = presensiHariIni.filter((p) => p.status === 'Hadir').length;
    const terlambat = presensiHariIni.filter((p) => p.status === 'Terlambat').length;
    const izin = presensiHariIni.filter((p) => p.status === 'Izin').length;
    const sakit = presensiHariIni.filter((p) => p.status === 'Sakit').length;
    const alpha = presensiHariIni.filter((p) => p.status === 'Alpha').length;

    return {
      totalPegawai, akunAktif, pendingApproval, pengajuanMenunggu,
      presensiHariIni: { hadir, terlambat, izin, sakit, alpha, total: presensiHariIni.length },
    };
  }, []);

  const chart = useMemo(() => {
    const { hadir, terlambat, izin, sakit, alpha, total } = stats.presensiHariIni;
    const pct = (n) => (total === 0 ? 0 : Math.round((n / total) * 100));
    return [
      { label: 'Hadir',     value: hadir,     pct: pct(hadir),     color: 'bg-success' },
      { label: 'Terlambat', value: terlambat, pct: pct(terlambat), color: 'bg-warning' },
      { label: 'Izin',      value: izin,      pct: pct(izin),      color: 'bg-info' },
      { label: 'Sakit',     value: sakit,     pct: pct(sakit),     color: 'bg-info' },
      { label: 'Alpha',     value: alpha,     pct: pct(alpha),     color: 'bg-danger' },
    ];
  }, [stats]);

  return (
    <div className="space-y-6">
      {/* ===== 4 STAT CARD SEJAJAR ===== */}
      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <StatCard icon={<Users size={20} />} label="Total Pegawai" value={stats.totalPegawai} tone="navy" hint="Guru & karyawan" />
        <StatCard icon={<UserCheck size={20} />} label="Akun Aktif" value={stats.akunAktif} tone="success" hint="Dapat login" />
        <StatCard icon={<Clock size={20} />} label="Pending Approval" value={stats.pendingApproval} tone="warning" hint="Menunggu review" onClick={() => navigate('/data-guru')} />
        <StatCard icon={<Mail size={20} />} label="Pengajuan Izin" value={stats.pengajuanMenunggu} tone="info" hint="Perlu diproses" onClick={() => navigate('/kotak-masuk')} />
      </div>

      {/* ===== 2 KOLOM: CHART + PENDAFTARAN ===== */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Chart (2/3) */}
        <Card className="lg:col-span-2">
          <div className="flex items-center justify-between mb-4">
            <div>
              <h3 className="font-bold text-gray-900 text-lg">Presensi Hari Ini</h3>
              <p className="text-xs text-gray-500">Total {stats.presensiHariIni.total} catatan</p>
            </div>
            <TrendingUp size={22} className="text-navy-500" />
          </div>
          <div className="space-y-3">
            {chart.map((c) => (
              <div key={c.label}>
                <div className="flex justify-between text-sm mb-1.5">
                  <span className="text-gray-600 font-medium">{c.label}</span>
                  <span className="text-gray-900 font-semibold">{c.value} <span className="text-gray-400 text-xs">({c.pct}%)</span></span>
                </div>
                <div className="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                  <div className={`h-full ${c.color} rounded-full transition-all`} style={{ width: `${c.pct}%` }} />
                </div>
              </div>
            ))}
          </div>
        </Card>

        {/* Pending list (1/3) */}
        <Card>
          <div className="flex justify-between items-center mb-4">
            <h3 className="font-bold text-gray-900">Menunggu Persetujuan</h3>
            <button onClick={() => navigate('/data-guru')} className="text-xs text-navy-500 font-semibold flex items-center gap-1 hover:underline">
              Lihat <ArrowRight size={12} />
            </button>
          </div>
          {initialGuru.filter((g) => g.status === 'pending').length === 0 ? (
            <p className="text-sm text-gray-500 text-center py-6">Tidak ada pendaftaran baru.</p>
          ) : (
            <div className="space-y-3">
              {initialGuru.filter((g) => g.status === 'pending').map((g) => (
                <div key={g.id} className="flex items-start justify-between gap-2 pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                  <div className="min-w-0">
                    <p className="text-sm font-semibold text-gray-800 truncate">{g.nama}</p>
                    <p className="text-xs text-gray-500 truncate">{g.jabatan} · {g.nip}</p>
                  </div>
                  <Badge status={g.status} />
                </div>
              ))}
            </div>
          )}
        </Card>
      </div>
    </div>
  );
}

function StatCard({ icon, label, value, tone, hint, onClick }) {
  const toneMap = {
    navy:    'bg-navy-50 text-navy-700',
    success: 'bg-green-50 text-green-700',
    warning: 'bg-orange-50 text-orange-700',
    info:    'bg-blue-50 text-blue-700',
  };
  return (
    <Card onClick={onClick}>
      <div className="flex items-start justify-between mb-3">
        <div className={`w-10 h-10 rounded-xl ${toneMap[tone]} flex items-center justify-center`}>
          {icon}
        </div>
        {onClick && <ArrowRight size={16} className="text-gray-400" />}
      </div>
      <p className="text-4xl font-extrabold text-gray-900 leading-tight">{value}</p>
      <p className="text-sm text-gray-700 font-medium mt-1">{label}</p>
      <p className="text-xs text-gray-400 mt-0.5">{hint}</p>
    </Card>
  );
}