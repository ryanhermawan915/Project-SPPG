import { useMemo, useState } from 'react';
import { Search, Plus, Pencil, UserCheck, UserX, CheckCircle2, PowerOff, Power } from 'lucide-react';
import Card from '../components/Card';
import Badge from '../components/Badge';
import Button from '../components/Button';
import Modal from '../components/Modal';
import { initialGuru } from '../data/dummyData';

export default function DataGuru() {
  const [guru, setGuru] = useState(initialGuru);
  const [search, setSearch] = useState('');
  const [filter, setFilter] = useState('semua');
  const [toast, setToast] = useState(null);
  const [confirm, setConfirm] = useState(null);

  const filtered = useMemo(() => {
    const q = search.toLowerCase();
    return guru.filter((g) => {
      const matchSearch = g.nama.toLowerCase().includes(q) || g.nip.includes(q) || g.email.toLowerCase().includes(q);
      const matchFilter = filter === 'semua' || g.status === filter;
      return matchSearch && matchFilter;
    });
  }, [guru, search, filter]);

  const askConfirm = (type, data) => setConfirm({ type, data });
  const closeConfirm = () => setConfirm(null);

  const handleConfirm = () => {
    if (!confirm) return;
    const { type, data } = confirm;
    if (type === 'approve') {
      setGuru((prev) => prev.map((g) => g.id === data.id ? { ...g, status: 'aktif' } : g));
      setToast(`${data.nama} disetujui.`);
    } else if (type === 'reject') {
      setGuru((prev) => prev.map((g) => g.id === data.id ? { ...g, status: 'nonaktif' } : g));
      setToast(`${data.nama} ditolak.`);
    } else if (type === 'toggle') {
      const next = data.status === 'aktif' ? 'nonaktif' : 'aktif';
      setGuru((prev) => prev.map((g) => g.id === data.id ? { ...g, status: next } : g));
      setToast(`${data.nama} → ${next}.`);
    }
    setConfirm(null);
    setTimeout(() => setToast(null), 2200);
  };

  return (
    <div className="space-y-4">
      {/* Toolbar */}
      <Card className="!p-4">
        <div className="flex flex-col lg:flex-row lg:items-center gap-3">
          <div className="relative flex-1">
            <Search size={18} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari nama, NIP, atau email…"
              className="w-full h-10 pl-10 pr-3 rounded-lg border border-gray-300 bg-white text-sm outline-none focus:border-navy-500 focus:ring-2 focus:ring-navy-100"
            />
          </div>
          <div className="flex items-center gap-2">
            {['semua', 'aktif', 'pending', 'nonaktif'].map((f) => (
              <button
                key={f}
                onClick={() => setFilter(f)}
                className={`h-10 px-4 rounded-lg text-sm font-medium capitalize transition ${
                  filter === f ? 'bg-navy-500 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'
                }`}
              >
                {f === 'semua' ? 'Semua' : f}
              </button>
            ))}
            <Button className="!w-auto !h-10">
              <Plus size={16} /> Tambah
            </Button>
          </div>
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
                <th>Jabatan</th>
                <th>Email</th>
                <th>Status</th>
                <th className="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 ? (
                <tr>
                  <td colSpan={6} className="text-center py-10 text-gray-500">
                    Tidak ada pegawai yang cocok.
                  </td>
                </tr>
              ) : (
                filtered.map((g) => (
                  <tr key={g.id}>
                    <td>
                      <div className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-full bg-navy-100 text-navy-700 flex items-center justify-center font-bold text-xs shrink-0">
                          {g.nama[0]}
                        </div>
                        <span className="font-semibold text-gray-900">{g.nama}</span>
                      </div>
                    </td>
                    <td className="text-gray-600">{g.nip}</td>
                    <td className="text-gray-600">{g.jabatan}</td>
                    <td className="text-gray-600">{g.email}</td>
                    <td><Badge status={g.status} /></td>
                    <td>
                      <div className="flex items-center justify-end gap-2">
                        {g.status === 'pending' ? (
                          <>
                            <button
                              onClick={() => askConfirm('approve', g)}
                              className="h-8 px-3 rounded-lg bg-green-50 text-green-700 text-xs font-semibold hover:bg-green-100 flex items-center gap-1"
                            >
                              <UserCheck size={14} /> Setujui
                            </button>
                            <button
                              onClick={() => askConfirm('reject', g)}
                              className="h-8 px-3 rounded-lg bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100 flex items-center gap-1"
                            >
                              <UserX size={14} /> Tolak
                            </button>
                          </>
                        ) : (
                          <>
                            <button className="h-8 px-3 rounded-lg border border-gray-300 text-gray-700 text-xs font-semibold hover:bg-gray-50 flex items-center gap-1">
                              <Pencil size={14} /> Edit
                            </button>
                            <button
                              onClick={() => askConfirm('toggle', g)}
                              className={`h-8 px-3 rounded-lg text-xs font-semibold flex items-center gap-1 ${
                                g.status === 'aktif'
                                  ? 'bg-red-50 text-red-700 hover:bg-red-100'
                                  : 'bg-green-50 text-green-700 hover:bg-green-100'
                              }`}
                            >
                              {g.status === 'aktif' ? <><PowerOff size={14} /> Nonaktifkan</> : <><Power size={14} /> Aktifkan</>}
                            </button>
                          </>
                        )}
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </Card>

      <Modal
        open={!!confirm}
        title={
          confirm?.type === 'approve' ? 'Setujui pendaftaran?' :
          confirm?.type === 'reject'  ? 'Tolak pendaftaran?' :
          confirm?.data?.status === 'aktif' ? 'Nonaktifkan akun?' : 'Aktifkan akun?'
        }
        message={
          confirm?.type === 'approve'
            ? `${confirm?.data?.nama} akan diaktifkan dan bisa login ke aplikasi.`
            : confirm?.type === 'reject'
            ? `${confirm?.data?.nama} akan ditolak. Tindakan ini dapat diubah kemudian.`
            : `Akun ${confirm?.data?.nama} akan ${confirm?.data?.status === 'aktif' ? 'dinonaktifkan' : 'diaktifkan'}.`
        }
        variant={confirm?.type === 'approve' ? 'success' : 'danger'}
        confirmText="Ya, lanjutkan"
        onCancel={closeConfirm}
        onConfirm={handleConfirm}
      />
    </div>
  );
}