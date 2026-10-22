import { CheckCircle2, AlertTriangle, Info, X } from "lucide-react";

const styles = {
    success: { bg: "bg-green-50 border-success text-success", Icon: CheckCircle2 },
    error: { bg: "bg-red-50 border-red-500 text-red-600", Icon: AlertTriangle },
    info: { bg: "bg-blue-50 border-primary text-primary", Icon: Info },
    };

    export default function Toast({ message, type = "success", onClose }) {
    const { bg, Icon } = styles[type] ?? styles.info;
    return (
        <div className={`flex items-start gap-2 rounded-card border px-4 py-3 shadow-soft text-sm max-w-sm ${bg}`}>
        <Icon className="w-4 h-4 mt-0.5 shrink-0" />
        <span className="flex-1 text-ink">{message}</span>
        <button onClick={onClose} aria-label="Tutup notifikasi"><X className="w-4 h-4" /></button>
        </div>
    );
}