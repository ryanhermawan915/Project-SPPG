import { useLocation } from 'react-router-dom';

const routeTitles = {
  '/dashboard':   'Dashboard',
  '/data-guru':   'Data Guru & Karyawan',
  '/verifikasi':  'Verifikasi Presensi',
  '/kotak-masuk': 'Kotak Masuk',
  '/jadwal':      'Manajemen Jadwal',
};

export default function Topbar() {
  const { pathname } = useLocation();
  const title = routeTitles[pathname] ?? 'Admin';
  const today = new Date().toLocaleDateString('id-ID', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
  });

  return (
    <header className="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 lg:px-8 sticky top-0 z-30">
      <div>
        <h2 className="text-base font-bold text-gray-900">{title}</h2>
        <p className="text-xs text-gray-500">{today}</p>
      </div>
      <div className="flex items-center gap-2">
        <span className="hidden sm:inline text-xs text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full">
          Administrator
        </span>
      </div>
    </header>
  );
}