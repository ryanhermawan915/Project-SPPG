import { useState } from "react";
import { Outlet, useLocation } from "react-router-dom";
import Sidebar from "./Sidebar";
import Topbar from "./Topbar";

const titles = {
    "/": "Dashboard",
    "/verifikasi": "Verifikasi",
    "/data-guru": "Data Guru",
    "/kotak-masuk": "Kotak Masuk",
    "/jadwal": "Jadwal",
};

export default function AdminLayout() {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { pathname } = useLocation();

    return (
        <div className="min-h-screen flex">
        <Sidebar open={sidebarOpen} onClose={() => setSidebarOpen(false)} />
        <div className="flex-1 flex flex-col min-w-0">
            <Topbar title={titles[pathname] ?? "SPPG Admin"} onOpenSidebar={() => setSidebarOpen(true)} />
            <main className="flex-1 p-4 md:p-6">
            <Outlet />
            </main>
        </div>
        </div>
    );
}