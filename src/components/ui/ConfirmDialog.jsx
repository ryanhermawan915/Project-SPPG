import { X } from "lucide-react";

export default function ConfirmDialog({ open, title = "Konfirmasi", children, confirmLabel = "Ya", cancelLabel = "Batal", onConfirm, onCancel, danger = false }) {
    if (!open) return null;
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div className="absolute inset-0 bg-black/40" onClick={onCancel} />
        <div className="relative bg-card rounded-card border border-line shadow-soft w-full max-w-md p-5">
            <div className="flex items-start justify-between">
            <h3 className="font-semibold">{title}</h3>
            <button onClick={onCancel} aria-label="Tutup"><X className="w-4 h-4" /></button>
            </div>
            <div className="mt-3 text-sm text-ink/70">{children}</div>
            <div className="mt-5 flex justify-end gap-2">
            <button onClick={onCancel} className="px-4 py-2 text-sm rounded-lg border border-line hover:bg-surface">{cancelLabel}</button>
            <button onClick={onConfirm} className={`px-4 py-2 text-sm rounded-lg text-white ${danger ? "bg-red-600 hover:bg-red-700" : "bg-primary hover:bg-blue-700"}`}>{confirmLabel}</button>
            </div>
        </div>
        </div>
    );
}