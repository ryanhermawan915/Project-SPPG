/**
 * Badge status otomatis berwarna:
 * hadir / disetujui / aktif       → hijau
 * terlambat / menunggu / pending  → oranye
 * izin / sakit                    → biru
 * alpha / ditolak / nonaktif      → merah
 */
const colorMap = {
  hadir:     'bg-green-100 text-green-700',
  disetujui: 'bg-green-100 text-green-700',
  aktif:     'bg-green-100 text-green-700',
  terlambat: 'bg-orange-100 text-orange-700',
  menunggu:  'bg-orange-100 text-orange-700',
  pending:   'bg-orange-100 text-orange-700',
  izin:      'bg-blue-100 text-blue-700',
  sakit:     'bg-blue-100 text-blue-700',
  alpha:     'bg-red-100 text-red-700',
  ditolak:   'bg-red-100 text-red-700',
  nonaktif:  'bg-red-100 text-red-700',
};

export default function Badge({ status }) {
  const key = String(status).toLowerCase();
  return (
    <span className={`inline-block px-2.5 py-1 rounded-full text-xs font-semibold ${colorMap[key] || 'bg-gray-100 text-gray-700'}`}>
      {status}
    </span>
  );
}