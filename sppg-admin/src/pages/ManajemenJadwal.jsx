import { useState } from 'react';
import { Plus, Pencil, Save, CheckCircle2, Clock } from 'lucide-react';
import Card from '../components/Card';
import Button from '../components/Button';
import Input from '../components/Input';
import { initialJadwal } from '../data/dummyData';

const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

export default function ManajemenJadwal() {
  const [jadwal, setJadwal] = useState(initialJadwal);
  const [editing, setEditing] = useState(null);
  const [toast, setToast] = useState(null);

  const bukaTambah = () => {
    setEditing({
      id: null, nama: '', hari: 'Senin',
      jamMasuk: '07:00', jamPulang: '14:00',
      toleransi: 15, aktif: true,
    });
  };

  const simpan = () => {
    if (!editing) return;
    if (editing.jamPulang <= editing.jamMasuk) {
      alert('Jam pulang harus lebih akhir dari jam masuk.');
      return;
    }
    if (!editing.nama.trim()) {
      alert('Nama jadwal wajib diisi.');
      return;
    }
    if (editing.toleransi < 0) {
      alert('Toleransi tidak boleh negatif.');
      return;
    }

    if (editing.id == null) {
      setJadwal((prev) => [...prev, { ...editing, id: Date.now() }]);
      setToast('Jadwal baru ditambahkan.');
    } else {
      setJadwal((prev) => prev.map((j) => j.id === editing.id ? { ...editing } : j));
      setToast('Jadwal diperbarui.');
    }
    setEditing(null);
    setTimeout(() => setToast(null), 2200);
  };

  return (
    <div className="space-y-4">
      <div className="flex justify-end">
        <Button className="!w-auto" onClick={bukaTambah}>
          <Plus size={16} /> Tambah Jadwal
        </Button>
      </div>

      {toast && (
        <div className="p-3 rounded-xl bg-green-50 border border-green-200 text-sm text-green-700 flex items-center gap-2">
          <CheckCircle2 size={18} /> {toast}
        </div>
      )}

      {/* Grid kartu jadwal */}
      <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        {jadwal.map((j) => (
          <Card key={j.id}>
            <div className="flex justify-between items-start mb-3">
              <div>
                <p className="font-bold text-gray-900">{j.nama}</p>
                <p className="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                  <Clock size={12} /> {j.hari}
                </p>
              </div>
              <span className={`text-[11px] font-semibold px-2 py-1 rounded-full ${
                j.aktif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
              }`}>
                {j.aktif ? 'Aktif' : 'Nonaktif'}
              </span>
            </div>
            <div className="grid grid-cols-3 gap-2 text-xs mb-4">
              <div>
                <p className="text-gray-400">Masuk</p>
                <p className="font-semibold text-gray-800">{j.jamMasuk}</p>
              </div>
              <div>
                <p className="text-gray-400">Pulang</p>
                <p className="font-semibold text-gray-800">{j.jamPulang}</p>
              </div>
              <div>
                <p className="text-gray-400">Toleransi</p>
                <p className="font-semibold text-gray-800">{j.toleransi} mnt</p>
              </div>
            </div>
            <Button size="sm" variant="outline" onClick={() => setEditing({ ...j })}>
              <Pencil size={14} /> Edit Jadwal
            </Button>
          </Card>
        ))}
      </div>

      {/* Modal form */}
      {editing && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <div className="bg-white rounded-2xl w-full max-w-lg p-6 space-y-4">
            <h3 className="text-lg font-bold text-gray-900">
              {editing.id ? 'Edit Jadwal' : 'Tambah Jadwal'}
            </h3>

            <Input
              label="Nama Jadwal"
              value={editing.nama}
              onChange={(e) => setEditing({ ...editing, nama: e.target.value })}
              placeholder="cth. Jadwal Reguler Senin"
            />

            <div>
              <label className="text-sm font-medium text-gray-700 mb-1 block">Hari Kerja</label>
              <select
                value={editing.hari}
                onChange={(e) => setEditing({ ...editing, hari: e.target.value })}
                className="w-full h-11 px-3 rounded-xl border border-gray-300 text-sm bg-white outline-none focus:border-navy-500"
              >
                {HARI.map((h) => <option key={h} value={h}>{h}</option>)}
              </select>
            </div>

            <div className="grid grid-cols-2 gap-3">
              <Input
                label="Jam Masuk"
                type="time"
                value={editing.jamMasuk}
                onChange={(e) => setEditing({ ...editing, jamMasuk: e.target.value })}
              />
              <Input
                label="Jam Pulang"
                type="time"
                value={editing.jamPulang}
                onChange={(e) => setEditing({ ...editing, jamPulang: e.target.value })}
              />
            </div>

            <Input
              label="Toleransi (menit)"
              type="number"
              min="0"
              value={editing.toleransi}
              onChange={(e) => setEditing({ ...editing, toleransi: parseInt(e.target.value || '0') })}
            />

            <label className="flex items-center justify-between p-3 rounded-xl bg-gray-50 cursor-pointer">
              <span className="text-sm font-medium text-gray-700">Jadwal Aktif</span>
              <input
                type="checkbox"
                checked={editing.aktif}
                onChange={(e) => setEditing({ ...editing, aktif: e.target.checked })}
                className="w-5 h-5 accent-navy-500"
              />
            </label>

            <div className="flex gap-2 pt-1">
              <Button variant="outline" onClick={() => setEditing(null)}>Batal</Button>
              <Button onClick={simpan}>
                <Save size={16} /> Simpan
              </Button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}