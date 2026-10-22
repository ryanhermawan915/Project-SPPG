import { useMemo, useState } from "react";
import { dataGuru } from "../data/dummy";
import SearchFilter from "../components/ui/SearchFilter";
import DataTable from "../components/ui/DataTable";
import StatusBadge from "../components/ui/StatusBadge";
import DetailDrawer from "../components/ui/DetailDrawer";
import EmptyState from "../components/ui/EmptyState";

const PAGE_SIZE = 5;

export default function DataGuru() {
    const [q, setQ] = useState("");
    const [status, setStatus] = useState("all");
    const [page, setPage] = useState(1);
    const [selected, setSelected] = useState(null);

    const filtered = useMemo(() => {
        return dataGuru.filter((g) => {
        const matchQ = g.nama.toLowerCase().includes(q.toLowerCase()) || (g.nomorPegawai ?? "").toLowerCase().includes(q.toLowerCase());
        const matchStatus = status === "all" || g.status === status;
        return matchQ && matchStatus;
        });
    }, [q, status]);

    const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
    const currentPage = Math.min(page, totalPages);
    const rows = filtered.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE);

    const columns = [
        { key: "nama", label: "Nama" },
        { key: "nomorPegawai", label: "No. Pegawai" },
        { key: "email", label: "Email" },
        { key: "status", label: "Status Akun", render: (r) => <StatusBadge status={r.status} /> },
    ];

    return (
        <div className="space-y-4">
        <div>
            <h2 className="text-xl font-bold">Data Guru</h2>
            <p className="text-sm text-ink/60 mt-1">Daftar guru dan karyawan. Data di bawah adalah dummy.</p>
        </div>

        <SearchFilter value={q} onChange={(v) => { setQ(v); setPage(1); }} placeholder="Cari nama atau nomor pegawai…">
            <select
            value={status}
            onChange={(e) => { setStatus(e.target.value); setPage(1); }}
            className="px-3 py-2 rounded-lg border border-line bg-card text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
            >
            <option value="all">Semua Status</option>
            <option value="Aktif">Aktif</option>
            <option value="Nonaktif">Nonaktif</option>
            </select>
        </SearchFilter>

        <section className="bg-card rounded-card border border-line shadow-soft p-4">
            {rows.length === 0 ? (
            <EmptyState title="Tidak ada data" description="Tidak ada pegawai yang sesuai filter." />
            ) : (
            <>
                <DataTable columns={columns} rows={rows} onRowClick={setSelected} />
                <div className="flex items-center justify-between mt-3 text-sm">
                <p className="text-ink/50">Halaman {currentPage} dari {totalPages}</p>
                <div className="flex gap-2">
                    <button onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={currentPage === 1} className="px-3 py-1.5 rounded-lg border border-line disabled:opacity-50 hover:bg-surface">Sebelumnya</button>
                    <button onClick={() => setPage((p) => Math.min(totalPages, p + 1))} disabled={currentPage === totalPages} className="px-3 py-1.5 rounded-lg border border-line disabled:opacity-50 hover:bg-surface">Berikutnya</button>
                </div>
                </div>
            </>
            )}
        </section>

        <DetailDrawer open={!!selected} title="Detail Pegawai" onClose={() => setSelected(null)}>
            {selected && (
            <dl className="space-y-3 text-sm">
                <div><dt className="text-ink/50 text-xs">Nama</dt><dd>{selected.nama}</dd></div>
                <div><dt className="text-ink/50 text-xs">No. Pegawai</dt><dd>{selected.nomorPegawai}</dd></div>
                <div><dt className="text-ink/50 text-xs">Email</dt><dd>{selected.email}</dd></div>
                <div><dt className="text-ink/50 text-xs">Status</dt><dd><StatusBadge status={selected.status} /></dd></div>
            </dl>
            )}
        </DetailDrawer>
        </div>
    );
}