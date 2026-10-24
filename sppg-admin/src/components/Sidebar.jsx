import { NavLink, useNavigate } from 'react-router-dom';
import { LayoutDashboard, Users, ClipboardCheck, Mail, Calendar, LogOut, ShieldCheck } from 'lucide-react';
import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import Modal from './Modal';

const menus = [
  { to: '/dashboard',   label: 'Dashboard',            icon: LayoutDashboard },
  { to: '/data-guru',   label: 'Data Guru & Karyawan', icon: Users },
  { to: '/verifikasi',  label: 'Verifikasi Presensi',  icon: ClipboardCheck },
  { to: '/kotak-masuk', label: 'Kotak Masuk',          icon: Mail },
  { to: '/jadwal',      label: 'Manajemen Jadwal',     icon: Calendar },
];

export default function Sidebar() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [logoutOpen, setLogoutOpen] = useState(false);

  const handleLogout = () => {
    logout();
    navigate('/login', { replace: true });
  };

  return (
    <>
      {/* fixed left, lebar 64 (256px), full height */}
      <aside className="fixed top-0 left-0 w-64 h-screen bg-white border-r border-gray-200 flex flex-col z-40">
        {/* Brand */}
        <div className="h-16 flex items-center gap-3 px-5 border-b border-gray-100 shrink-0">
          <div className="w-9 h-9 rounded-xl bg-navy-500 flex items-center justify-center">
            <ShieldCheck size={20} className="text-white" />
          </div>
          <div>
            <p className="font-extrabold text-gray-900 leading-tight">SPPG Admin</p>
            <p className="text-[11px] text-gray-500">Presensi Guru & Karyawan</p>
          </div>
        </div>

        {/* Menu */}
        <nav className="flex-1 overflow-y-auto py-4 px-3 space-y-1">
          {menus.map(({ to, label, icon: Icon }) => (
            <NavLink
              key={to}
              to={to}
              className={({ isActive }) =>
                `flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition ${
                  isActive
                    ? 'bg-navy-50 text-navy-700 border-l-[3px] border-navy-500'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-l-[3px] border-transparent'
                }`
              }
            >
              <Icon size={18} />
              <span>{label}</span>
            </NavLink>
          ))}
        </nav>

        {/* User + Logout */}
        <div className="border-t border-gray-100 p-3 shrink-0">
          <div className="flex items-center gap-3 px-2 py-2 mb-1">
            <div className="w-9 h-9 rounded-full bg-navy-100 text-navy-700 flex items-center justify-center font-bold text-sm">
              {user?.name?.[0] ?? 'A'}
            </div>
            <div className="min-w-0">
              <p className="text-sm font-semibold text-gray-900 truncate">{user?.name ?? 'Admin'}</p>
              <p className="text-[11px] text-gray-500 truncate">{user?.email ?? '-'}</p>
            </div>
          </div>
          <button
            onClick={() => setLogoutOpen(true)}
            className="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-danger hover:bg-red-50 transition"
          >
            <LogOut size={18} />
            <span>Keluar</span>
          </button>
        </div>
      </aside>

      <Modal
        open={logoutOpen}
        title="Keluar dari aplikasi?"
        message="Anda perlu login kembali untuk mengakses dashboard admin."
        confirmText="Ya, Keluar"
        variant="danger"
        onCancel={() => setLogoutOpen(false)}
        onConfirm={handleLogout}
      />
    </>
  );
}