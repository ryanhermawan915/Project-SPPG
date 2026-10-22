import { useState } from "react";
import { Users, UserCheck, Clock, ClipboardList } from "lucide-react";
import StatCard from "../components/ui/StatCard";
import DataTable from "../components/ui/DataTable";
import StatusBadge from "../components/ui/StatusBadge";
import EmptyState from "../components/ui/EmptyState";
import DetailDrawer from "../components/ui/DetailDrawer";
import { dashboardStats, recentActivities } from "../data/dummy";

export default function Dashboard() {
    const [selected, setSelected] = useState(null);
    const [loading] = useState(false); // toggle manual untuk demo state

    const columns = [
        { key: "nama", label: "Nama Pegawai" },
        { key: "jenis", label: "Jenis Aktivitas" },
        { key: "waktu", label: "Tanggal/Waktu" },
        { key: "status", label: "Status", render: (r) => <StatusBadge status={r.status} /> },
        { key: "aksi", label: "", render: (r) => (
        <button onClick={(e) => { e.stopPropagation(); setSelected(r); }} className="text-primary text-xs font-medium hover:underline">Detail</button>
        )},
    ];

    return (
        <div className="space-y-6">
        <div>
            <h2 className="text-xl font-bold">Dashboard</h2>
            <p className="text-sm text-ink/60 mt-1">Ringkasan aktivitas presensi. Angka menunjukkan placeholder (dummy).</p>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <StatCard label="Total Guru & Karyawan" value={dashboardStats.totalPegawai} hint="placeholder" Icon={Users} />
            <StatCard label="Hadir Hari Ini" value={dashboardStats.hadirHariIni} hint="placeholder" Icon={UserCheck} tone="success" />
            <StatCard label="Terlambat" value={dashboardStats.terlambat} hint="placeholder" Icon={Clock} tone="warning" />
            <StatCard label="Perlu Diproses" value={dashboardStats.perluDiproses} hint="placeholder" Icon={ClipboardList} tone="notif" />
        </div>

        <section className="bg-card rounded-card border border-line shadow-soft">
            <div className="px-5 py-4 border-b border-line flex items-center justify-between">
            <h3 className="font-semibold">Aktivitas Terbaru</h3>
            <span className="text-xs text-ink/40">dummy</span>
            </div>
            <div className="p-4">
            {loading ? (
                <p className="py-6 text-center text-sm text-ink/50">Memuat…</p>
            ) : recentActivities.length === 0 ? (
                <EmptyState title="Belum ada aktivitas" description="Aktivitas presensi akan tampil setelah backend terhubung." />
            ) : (
                <DataTable columns={columns} rows={recentActivities} onRowClick={setSelected} />
            )}
            </div>
        </section>

        <DetailDrawer open={!!selected} title="Detail Aktivitas" onClose={() => setSelected(null)}>
            {selected && (
            <dl className="space-y-3 text-sm">
                <div><dt className="text-ink/50 text-xs">Nama</dt><dd>{selected.nama}</dd></div>
                <div><dt className="text-ink/50 text-xs">Jenis</dt><dd>{selected.jenis}</dd></div>
                <div><dt className="text-ink/50 text-xs">Waktu</dt><dd>{selected.waktu}</dd></div>
                <div><dt className="text-ink/50 text-xs">Status</dt><dd><StatusBadge status={selected.status} /></dd></div>
            </dl>
            )}
        </DetailDrawer>
        </div>
    );
}