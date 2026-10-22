import { Bell, Menu, UserCircle2, LogOut } from "lucide-react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../../context/AuthContext";

export default function Topbar({ title, onOpenSidebar }) {
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    function handleLogout() {
        logout();
        navigate("/login", { replace: true });
    }

    return (
        <header className="h-16 bg-card border-b border-line flex items-center gap-3 px-4 md:px-6">
        <button className="lg:hidden" onClick={onOpenSidebar} aria-label="Buka menu"><Menu className="w-5 h-5" /></button>
        <h1 className="text-base md:text-lg font-semibold flex-1 truncate">{title}</h1>

        <button className="relative p-2 rounded-lg hover:bg-surface" aria-label="Notifikasi">
            <Bell className="w-5 h-5 text-ink/70" />
            <span className="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-notif" />
        </button>

        <div className="flex items-center gap-2">
            <UserCircle2 className="w-7 h-7 text-primary" />
            <div className="hidden md:block text-xs leading-tight">
            <p className="font-semibold">{user?.name ?? "Admin"}</p>
            <p className="text-ink/50">{user?.role ?? ""}</p>
            </div>
            <button onClick={handleLogout} className="ml-1 p-2 rounded-lg hover:bg-surface text-ink/60 hover:text-red-600" aria-label="Keluar" title="Keluar">
            <LogOut className="w-4 h-4" />
            </button>
        </div>
        </header>
    );
}