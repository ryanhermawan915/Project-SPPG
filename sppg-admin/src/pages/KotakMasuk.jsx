import { useMemo, useState } from 'react';
import { FileText, CheckCircle2, XCircle, Inbox } from 'lucide-react';
import Card from '../components/Card';
import Badge from '../components/Badge';
import Button from '../components/Button';
import Modal from '../components/Modal';
import { initialPengajuan } from '../data/dummyData';

export default function KotakMasuk() {
  const [pengajuan, setPengajuan] = useState(initialPengajuan);
  const [tab, setTab] = useState('Menunggu');
  const [detail, setDetail] = useState(null);
  const [confirm, setConfirm] = useState(null);
  const [catatan, setCatatan] = useState('');
  const [toast, setToast] = useState(null);

  const list = useMemo(
    () => tab === 'Menunggu' ? pengajuan.filter((p) => p.status === 'Menunggu') : pengajuan,
    [pengajuan, tab]
  );

  const bukaDetail = (item) => {
    setDetail(item);
    setCatatan(item.catatanAdmin || '');
  };

  const proses = (type) => {
    if (!detail) return;
    setConfirm({ type, data: detail });
  };

  const konfirmasi = () => {
    if (!confirm) return;
    const newStatus = confirm.type === 'approve' ? 'Disetujui' : 'Ditolak';
    setPengajuan((prev) =>
      prev.map((p) => p.id === confirm.data.id ? { ...p, status: newStatus, catatanAdmin: catatan } : p)
    );
    setToast(`Pengajuan ${confirm.data.nama} ${newStatus.toLowerCase()}.`);
    setConfirm(null);
    setDetail(null);
    setCatatan('');
    setTimeout(() => setToast(null), 2500);
  };

  return (
    <div className="space-y-4">
      {/* Tab */}
      <div className="flex gap-2">
        {[
          { key: 'Menunggu', label: 'Menunggu' },
          { key: 'Semua', label: 'Semua' },
        ].map((t) => (
          <button
            key={t.key}
            onClick={() => setTab(t.key)}
            className={`h-10 px-4 rounded-lg text-sm font-semibold transition ${
              tab === t.key ? 'bg-navy-500 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'
            }`}
          >
            {t.label}
            {t.key === 'Menunggu' && (
              <span className="ml-1.5 text-xs opacity-80">
                ({pengajuan.filter((p) => p.status === 'Menunggu').length})
              </span>
            )}
          </button>
        ))}
      </div>

      {toast && (
        <div className="p-3 rounded-xl bg-green-50 border border-green-200 text-sm text-green-700 flex items-center gap-2">
          <CheckCircle2 size={18} /> {toast}
        </div>
      )}

      {/* 2 kolom: list kiri, detail kanan */}
      <div className="grid grid-cols-1 lg:grid-cols-5 gap-4">
        {/* List */}
        <Card className="lg:col-span-2 !p-0 overflow-hidden">
          <div className="divide-y divide-gray-100 max-h-[70vh] overflow-y-auto">
            {list.length === 0 ? (
              <div className="text-center py-12 text-gray-500">
                <Inbox size={32} className="mx-auto mb-2 text-gray-300" />
                <p className="text-sm">Tidak ada pengajuan.</p>
              </div>
            ) : (
              list.map((p) => (
                <button
                  key={p.id}
                  onClick={() => bukaDetail(p)}
                  className={`w-full text-left p-4 hover:bg-navy-50/40 transition ${
                    detail?.id === p.id ? 'bg-navy-50 border-l-[3px] border-navy-500' : ''
                  }`}
                >
                  <div className="flex justify-between items-start gap-2 mb-1">
                    <p className="font-semibold text-sm text-gray-900 truncate">{p.nama}</p>
                    <Badge status={p.status} />
                  </div>
                  <p className="text-xs text-gray-500 mb-1.5">{p.nip}</p>
                  <div className="flex items-center gap-2">
                    <Badge status={p.jenis} />
                    <span className="text-xs text-gray-500 truncate">{p.tanggalMulai} — {p.tanggalSelesai}</span>
                  </div>
                  <p className="text-xs text-gray-600 line-clamp-2 mt-2">{p.alasan}</p>
                </button>
              ))
            )}
          </div>
        </Card>

        {/* Detail */}
        <Card className="lg:col-span-3">
          {!detail ? (
            <div className="text-center py-16 text-gray-500">
              <FileText size={40} className="mx-auto mb-3 text-gray-300" />
              <p className="text-sm">Pilih pengajuan dari daftar untuk melihat detail.</p>
            </div>
          ) : (
            <div className="space-y-4">
              <div className="flex justify-between items-start">
                <div>
                  <h3 className="text-lg font-bold text-gray-900">Detail Pengajuan</h3>
                  <p className="text-sm text-gray-500">{detail.nama} · {detail.nip}</p>
                </div>
                <Badge status={detail.status} />
              </div>

              <div className="grid grid-cols-2 gap-3 text-sm">
                <Info label="Jenis" value={detail.jenis} />
                <Info label="Tanggal Pengajuan" value={detail.tanggalPengajuan} />
                <Info label="Mulai" value={detail.tanggalMulai} />
                <Info label="Selesai" value={detail.tanggalSelesai} />
              </div>

              <div>
                <p className="text-xs text-gray-400 mb-1">Alasan</p>
                <p className="text-sm text-gray-800 bg-gray-50 p-3 rounded-xl">{detail.alasan}</p>
              </div>

              <div>
                <p className="text-xs text-gray-400 mb-1">Lampiran</p>
                {detail.lampiran ? (
                  <div className="flex items-center gap-2 p-3 rounded-xl border border-gray-200 bg-white">
                    <FileText size={18} className="text-navy-500" />
                    <span className="text-sm text-gray-700 flex-1 truncate">{detail.lampiran}</span>
                    <button className="text-xs text-navy-500 font-semibold hover:underline">Lihat</button>
                  </div>
                ) : (
                  <p className="text-sm text-gray-500">Tidak ada lampiran.</p>
                )}
              </div>

              {detail.status === 'Menunggu' ? (
                <>
                  <div>
                    <label className="text-xs text-gray-400 mb-1 block">Catatan Admin (opsional)</label>
                    <textarea
                      value={catatan}
                      onChange={(e) => setCatatan(e.target.value)}
                      rows={2}
                      placeholder="Tambahkan catatan keputusan…"
                      className="w-full p-3 rounded-xl border border-gray-300 text-sm outline-none focus:border-navy-500 resize-none"
                    />
                  </div>
                  <div className="flex gap-2">
                    <Button variant="danger" onClick={() => proses('reject')}>
                      <XCircle size={16} /> Tolak
                    </Button>
                    <Button variant="success" onClick={() => proses('approve')}>
                      <CheckCircle2 size={16} /> Setujui
                    </Button>
                  </div>
                </>
              ) : (
                <div>
                  <p className="text-xs text-gray-400 mb-1">Catatan Admin</p>
                  <p className="text-sm text-gray-700 bg-gray-50 p-3 rounded-xl">
                    {detail.catatanAdmin || '-'}
                  </p>
                </div>
              )}
            </div>
          )}
        </Card>
      </div>

      <Modal
        open={!!confirm}
        title={confirm?.type === 'approve' ? 'Setujui pengajuan?' : 'Tolak pengajuan?'}
        message={
          confirm?.type === 'approve'
            ? `Pengajuan ${confirm?.data?.jenis} dari ${confirm?.data?.nama} akan disetujui.`
            : `Pengajuan ${confirm?.data?.jenis} dari ${confirm?.data?.nama} akan ditolak.`
        }
        variant={confirm?.type === 'approve' ? 'success' : 'danger'}
        confirmText="Ya, lanjutkan"
        onCancel={() => setConfirm(null)}
        onConfirm={konfirmasi}
      />
    </div>
  );
}

function Info({ label, value }) {
  return (
    <div>
      <p className="text-xs text-gray-400">{label}</p>
      <p className="font-medium text-gray-800">{value}</p>
    </div>
  );
}