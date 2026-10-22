import { useState } from "react";
import { pengajuanIzin, koreksiPresensi, notifikasiSistem } from "../data/dummy";
import DataTable from "../components/ui/DataTable";
import StatusBadge from "../components/ui/StatusBadge";
import DetailDrawer from "../components/ui/DetailDrawer";
import ConfirmDialog from "../components/ui/ConfirmDialog";
import EmptyState from "../components/ui/EmptyState";
import { useToast } from "../components/ToastProvider";

const tabs = [
    { key: "izin", label: "Pengajuan Izin/Sakit" },
    { key: "koreksi", label: "Koreksi Presensi" },
    { key: "notif", label: "Notifikasi" },
    ];

    export default function KotakMasuk() {
    const [tab, setTab] = useState("izin");
    const [selected, setSelected] = useState(null);
    const [confirm, setConfirm] = useState(null);
    const toast = useToast();

    const izinColumns = [
        { key: "pegawai", label: "Pegawai" },
        { key: "jenis", label: "Jenis" },
        { key: "tanggal", label: "Tanggal" },
        { key: "status", label: "Status", render: (r) => <StatusBadge status={r.status} /> },
    ];

    const koreksiColumns = [
        { key: "pegawai", label: "Pegawai" },
        { key: "tanggal", label: "Tanggal" },
        { key: "waktuTercatat", label: "Tercatat" },
        { key: "waktuDiminta", label: "Diminta" },
        { key: "status", label: "Status", render: (r) => <StatusBadge status={r.status} /> },
    ];

    const notifColumns = [
        { key: "judul", label: "Judul" },
        { key: "pesan", label: "Pesan" },
        { key: "dibaca", label: "Status", render: (r) => <StatusBadge status={r.dibaca ? "Dibaca" : "Belum Dibaca"} /> },
    ];

    function handleConfirm() {
        toast.push("Simulasi: tindakan berhasil (tidak tersimpan ke database).", "success");
        setConfirm(null);
        setSelected(null);
    }

    return (
        <div className="space-y-4">
        <div>
            <h2 className="text-xl font-bold">Kotak Masuk</h2>
            <p className="text-sm text-ink/60 mt-1">Kelola pengajuan izin, koreksi presensi, dan notifikasi.</p>
        </div>

        <div className="flex gap-1 border-b border-line">
            {tabs.map((t) => (
            <button
                key={t.key}
                onClick={() => { setTab(t.key); setSelected(null); }}
                className={`px-4 py-2 text-sm font-medium border-b-2 -mb-px ${tab === t.key ? "border-primary text-primary" : "border-transparent text-ink/60 hover:text-ink"}`}
            >
                {t.label}
            </button>
            ))}
        </div>

        <section className="bg-card rounded-card border border-line shadow-soft p-4">
            {tab === "izin" && (pengajuanIzin.length === 0 ? (
            <EmptyState title="Belum ada pengajuan izin" description="Pengajuan akan tampil setelah tersedia." />
            ) : (
            <DataTable columns={izinColumns} rows={pengajuanIzin} onRowClick={setSelected} />
            ))}

            {tab === "koreksi" && (koreksiPresensi.length === 0 ? (
            <EmptyState title="Belum ada koreksi presensi" description="Koreksi akan tampil setelah tersedia." />
            ) : (
            <DataTable columns={koreksiColumns} rows={koreksiPresensi} onRowClick={setSelected} />
            ))}

            {tab === "notif" && (notifikasiSistem.length === 0 ? (
            <EmptyState title="Belum ada notifikasi" description="Notifikasi sistem akan tampil di sini." />
            ) : (
            <DataTable columns={notifColumns} rows={notifikasiSistem} onRowClick={setSelected} />
            ))}
        </section>

        <DetailDrawer
            open={!!selected}
            title="Detail"
            onClose={() => setSelected(null)}
            footer={tab !== "notif" && selected && (
            <>
                <button onClick={() => setConfirm({ type: "reject" })} className="px-4 py-2 text-sm rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Tolak</button>
                <button onClick={() => setConfirm({ type: "approve" })} className="px-4 py-2 text-sm rounded-lg bg-primary text-white hover:bg-blue-700">Setujui</button>
            </>
            )}
        >
            {selected && (
            <dl className="space-y-3 text-sm">
                {Object.entries(selected).map(([k, v]) => (
                <div key={k}>
                    <dt className="text-ink/50 text-xs capitalize">{k}</dt>
                    <dd>{String(v ?? "—")}</dd>
                </div>
                ))}
            </dl>
            )}
        </DetailDrawer>

        <ConfirmDialog
            open={!!confirm}
            title={confirm?.type === "approve" ? "Setujui Pengajuan?" : "Tolak Pengajuan?"}
            confirmLabel={confirm?.type === "approve" ? "Setujui" : "Tolak"}
            danger={confirm?.type === "reject"}
            onCancel={() => setConfirm(null)}
            onConfirm={handleConfirm}
        >
            <p>Ini aksi simulasi UI. Tindakan nyata harus diproses dan diaudit di backend.</p>
        </ConfirmDialog>
        </div>
    );
}