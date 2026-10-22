import { NavLink } from "react-router-dom";
import { LayoutDashboard, ShieldCheck, Users, Inbox, CalendarClock, X } from "lucide-react";

const menus = [
    { to: "/verifikasi", label: "Verifikasi", Icon: ShieldCheck },
    { to: "/data-guru", label: "Data Guru", Icon: Users },
    { to: "/", label: "Dashboard", Icon: LayoutDashboard },
    { to: "/kotak-masuk", label: "Kotak Masuk", Icon: Inbox },
    { to: "/jadwal", label: "Jadwal", Icon: CalendarClock },
];

export default function Sidebar({ open, onClose }) {
    return (
        <>
        {open && <div className="fixed inset-0 bg-black/30 z-30 lg:hidden" onClick={onClose} />}
        <aside className={`fixed inset-y-0 left-0 z-40 w-64 bg-card border-r border-line transform transition-transform duration-200 ${open ? "translate-x-0" : "-translate-x-full"} lg:translate-x-0 lg:static lg:z-auto`}>
            <div className="h-16 flex items-center justify-between px-5 border-b border-line">
            <span className="font-bold text-primary text-lg">SPPG Admin</span>
            <button className="lg:hidden" onClick={onClose} aria-label="Tutup menu"><X className="w-5 h-5" /></button>
            </div>
            <nav className="p-3 space-y-1">
            {menus.map(({ to, label, Icon }) => (
                <NavLink
                key={to}
                to={to}
                end={to === "/"}
                onClick={onClose}
                className={({ isActive }) =>
                    `flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition ${isActive ? "bg-primary text-white" : "text-ink/70 hover:bg-surface hover:text-ink"}`
                }
                >
                <Icon className="w-4 h-4" />
                {label}
                </NavLink>
            ))}
            </nav>
        </aside>
        </>
    );
}