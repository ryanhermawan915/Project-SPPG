import { useMemo, useState } from "react";
import { pendaftarMenunggu } from "../data/dummy";
import SearchFilter from "../components/ui/SearchFilter";
import DataTable from "../components/ui/DataTable";
import StatusBadge from "../components/ui/StatusBadge";
import DetailDrawer from "../components/ui/DetailDrawer";
import ConfirmDialog from "../components/ui/ConfirmDialog";
import EmptyState from "../components/ui/EmptyState";
import { useToast } from "../components/ToastProvider";

export default function Verifikasi() {
    const [q, setQ] = useState("");
    const [selected, setSelected] = useState(null);
    const [confirm, setConfirm] = useState(null); // { type: "approve" | "reject" }
    const [alasanTolak, setAlasanTolak] = useState("");
    const toast = useToast();

    const rows = useMemo(
        () => pendaftarMenunggu.filter((r) => r.nama.toLowerCase().includes(q.toLowerCase())),
        [q]
    );

    const columns = [
        { key: "nama", label: "Nama" },
        { key: "email", label: "Email" },
        { key: "nomorPegawai", label: "No. Pegawai" },
        { key: "tanggalDaftar", label: "Tanggal Daftar" },
        { key: "status", label: "Status", render: (r) => <StatusBadge status={r.status} /> },
    ];

    function handleConfirm() {
        if (confirm?.type === "reject" && !alasanTolak.trim()) {
        toast.push("Alasan penolakan wajib diisi.", "error");
        return;
        }
        // ⚠️ SIMULASI — tidak tersimpan ke database.
        toast.push(confirm?.type === "approve" ? "Simulasi: pendaftar disetujui." : "Simulasi: pendaftar ditolak.", "success");
        setConfirm(null);
        setAlasanTolak("");
        setSelected(null);
    }

    return (
        <div className="space-y-4">
        <div>
            <h2 className="text-xl font-bold">Verifikasi</h2>
            <p className="text-sm text-ink/60 mt-1">Pendaftaran akun guru/karyawan yang menunggu pemeriksaan.</p>
        </div>

        <SearchFilter value={q} onChange={setQ} placeholder="Cari nama pendaftar…" />

        <section className="bg-card rounded-card border border-line shadow-soft p-4">
            {rows.length === 0 ? (
            <EmptyState title="Tidak ada pendaftar" description="Semua pendaftaran telah diproses atau belum ada data." />
            ) : (
            <DataTable columns={columns} rows={rows} onRowClick={setSelected} />
            )}
        </section>

        <DetailDrawer
            open={!!selected}
            title="Detail Pendaftar"
            onClose={() => setSelected(null)}
            footer={selected && (
            <>
                <button onClick={() => setConfirm({ type: "reject" })} className="px-4 py-2 text-sm rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Tolak</button>
                <button onClick={() => setConfirm({ type: "approve" })} className="px-4 py-2 text-sm rounded-lg bg-primary text-white hover:bg-blue-700">Setujui</button>
            </>
            )}
        >
            {selected && (
            <dl className="space-y-3 text-sm">
                <div><dt className="text-ink/50 text-xs">ID</dt><dd>{selected.id}</dd></div>
                <div><dt className="text-ink/50 text-xs">Nama</dt><dd>{selected.nama}</dd></div>
                <div><dt className="text-ink/50 text-xs">Email</dt><dd>{selected.email}</dd></div>
                <div><dt className="text-ink/50 text-xs">No. Pegawai</dt><dd>{selected.nomorPegawai}</dd></div>
                <div><dt className="text-ink/50 text-xs">Tanggal Daftar</dt><dd>{selected.tanggalDaftar}</dd></div>
                <div><dt className="text-ink/50 text-xs">Status</dt><dd><StatusBadge status={selected.status} /></dd></div>
            </dl>
            )}
        </DetailDrawer>

        <ConfirmDialog
            open={!!confirm}
            title={confirm?.type === "approve" ? "Setujui Pendaftar?" : "Tolak Pendaftar?"}
            confirmLabel={confirm?.type === "approve" ? "Setujui" : "Tolak"}
            danger={confirm?.type === "reject"}
            onCancel={() => { setConfirm(null); setAlasanTolak(""); }}
            onConfirm={handleConfirm}
        >
            {confirm?.type === "approve" ? (
            <p>Pendaftar akan disetujui (simulasi UI). Tindakan nyata harus divalidasi backend.</p>
            ) : (
            <div className="space-y-2">
                <p>Penolakan memerlukan alasan (wajib).</p>
                <textarea
                value={alasanTolak}
                onChange={(e) => setAlasanTolak(e.target.value)}
                rows={3}
                placeholder="Tuliskan alasan penolakan…"
                className="w-full rounded-lg border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                />
            </div>
            )}
        </ConfirmDialog>
        </div>
    );
}