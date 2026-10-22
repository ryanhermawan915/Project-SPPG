import { X } from "lucide-react";

export default function DetailDrawer({ open, title, children, onClose, footer }) {
    return (
        <>
        {open && <div className="fixed inset-0 bg-black/30 z-40" onClick={onClose} />}
        <aside className={`fixed top-0 right-0 h-full w-full sm:w-96 bg-card border-l border-line z-50 shadow-soft transform transition-transform duration-200 ${open ? "translate-x-0" : "translate-x-full"}`}>
            <div className="h-16 flex items-center justify-between px-5 border-b border-line">
            <h3 className="font-semibold">{title}</h3>
            <button onClick={onClose} aria-label="Tutup"><X className="w-5 h-5" /></button>
            </div>
            <div className="p-5 overflow-y-auto" style={{ height: footer ? "calc(100% - 8rem)" : "calc(100% - 4rem)" }}>
            {children}
            </div>
            {footer && <div className="h-16 px-5 border-t border-line flex items-center justify-end gap-2">{footer}</div>}
        </aside>
        </>
    );
}