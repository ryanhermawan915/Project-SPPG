const map = {
    Aktif: "bg-green-50 text-success border-success/30",
    Menunggu: "bg-amber-50 text-warning border-warning/30",
    Nonaktif: "bg-slate-100 text-slate-600 border-line",
    Disetujui: "bg-green-50 text-success border-success/30",
    Ditolak: "bg-red-50 text-red-600 border-red-300",
    Hadir: "bg-green-50 text-success border-success/30",
    Terlambat: "bg-amber-50 text-warning border-warning/30",
    Dibaca: "bg-slate-100 text-slate-600 border-line",
    };

    export default function StatusBadge({ status }) {
    const cls = map[status] ?? "bg-slate-100 text-slate-600 border-line";
    return (
        <span className={`inline-flex items-center px-2 py-0.5 rounded-full border text-xs font-medium ${cls}`}>
        {status}
        </span>
    );
}