import { Outlet } from 'react-router-dom';
import Sidebar from '../components/Sidebar';
import Topbar from '../components/Topbar';

/**
 * Layout desktop murni:
 *   [Sidebar kiri fixed] [Topbar + Content]
 * Tidak ada BottomNav.
 */
export default function AdminLayout() {
  return (
    <div className="min-h-screen bg-pageBg flex">
      <Sidebar />
      <div className="flex-1 min-w-0 flex flex-col ml-64">
        <Topbar />
        <main className="flex-1 p-6 lg:p-10">
          <div className="max-w-[1600px] mx-auto">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
}