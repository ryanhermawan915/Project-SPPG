import { useMemo, useState } from 'react';
import { Search, Pencil, CheckCircle2 } from 'lucide-react';
import Card from '../components/Card';
import Badge from '../components/Badge';
import Button from '../components/Button';
import { initialPresensi } from '../data/dummyData';

const STATUS_OPTIONS = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha'];

export default function VerifikasiPresensi() {
  const [data, setData] = useState(initialPresensi);
  const [search, setSearch] = useState('');
  const [tanggal, setTanggal] = useState('');
  const [statusFilter, setStatusFilter] = useState('semua');
  const [editing, setEditing] = useState(null);
  const [toast, setToast] = useState(null);

  const filtered = useMemo(() => {
    const q = search.toLowerCase();
    return data.filter((p) => {
      const matchSearch = p.nama.toLowerCase().includes(q) || p.nip.includes(q);
      const matchTanggal = !tanggal || p.tanggal === tanggal;
      const matchStatus = statusFilter === 'semua' || p.status === statusFilter;
      return matchSearch && matchTanggal && matchStatus;
    });
  }, [data, search, tanggal, statusFilter]);

  const simpanPerubahan = () => {
    if (!editing) return;
    setData((prev) => prev.map((p) => p.id === editing.id ? { ...p, status: editing.status } : p));
    setToast(`Status ${editing.nama} diubah menjadi ${editing.status}.`);
    setEditing(null);
    setTimeout(() => setToast(null), 2500);
  };

  return (
    <div className="space-y-4">
      {/* Filter bar */}
      <Card className="!p-4">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-3">
          <div className="md:col-span-2 relative">
            <Search size={18} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari nama atau NIP…"
              className="w-full h-10 pl-10 pr-3 rounded-lg border border-gray-300 text-sm outline-none focus:border-navy-500"
            />
          </div>
          <input
            type="date"
            value={tanggal}
            onChange={(e) => setTanggal(e.target.value)}
            className="h-10 px-3 rounded-lg border border-gray-300 text-sm outline-none focus:border-navy-500"
          />
          <select
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            className="h-10 px-3 rounded-lg border border-gray-300 text-sm bg-white outline-none focus:border-navy-500"
          >
            <option value="semua">Semua Status</option>
            {STATUS_OPTIONS.map((s) => <option key={s} value={s}>{s}</option>)}
          </select>
        </div>
      </Card>

      {toast && (
        <div className="p-3 rounded-xl bg-green-50 border border-green-200 text-sm text-green-700 flex items-center gap-2">
          <CheckCircle2 size={18} /> {toast}
        </div>
      )}

      {/* Tabel */}
      <Card className="!p-0 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="table-base">
            <thead>
              <tr>
                <th>Nama</th>
                <th>NIP</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Status</th>
                <th className="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 ? (
                <tr>
                  <td colSpan={7} className="text-center py-10 text-gray-500">
                    Tidak ada catatan presensi.
                  </td>
                </tr>
              ) : (
                filtered.map((p) => (
                  <tr key={p.id}>
                    <td className="font-semibold text-gray-900">{p.nama}</td>
                    <td className="text-gray-600">{p.nip}</td>
                    <td className="text-gray-600">{p.tanggal}</td>
                    <td className="text-gray-600">{p.jamMasuk}</td>
                    <td className="text-gray-600">{p.jamPulang}</td>
                    <td><Badge status={p.status} /></td>
                    <td>
                      <div className="flex justify-end">
                        <button
                          onClick={() => setEditing({ ...p })}
                          className="h-8 px-3 rounded-lg border border-gray-300 text-gray-700 text-xs font-semibold hover:bg-gray-50 flex items-center gap-1"
                        >
                          <Pencil size={14} /> Koreksi
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </Card>

      {/* Modal edit */}
      {editing && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <div className="bg-white rounded-2xl w-full max-w-md p-6 space-y-4">
            <div>
              <h3 className="text-lg font-bold text-gray-900">Koreksi Status</h3>
              <p className="text-sm text-gray-500">{editing.nama} · {editing.tanggal}</p>
            </div>
            <div className="grid grid-cols-2 gap-2">
              {STATUS_OPTIONS.map((s) => (
                <button
                  key={s}
                  onClick={() => setEditing({ ...editing, status: s })}
                  className={`h-11 rounded-xl text-sm font-semibold border-2 transition ${
                    editing.status === s
                      ? 'border-navy-500 bg-navy-50 text-navy-700'
                      : 'border-gray-200 text-gray-600 hover:border-gray-300'
                  }`}
                >
                  {s}
                </button>
              ))}
            </div>
            <div className="flex gap-2">
              <Button variant="outline" onClick={() => setEditing(null)}>Batal</Button>
              <Button onClick={simpanPerubahan}>Simpan</Button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}