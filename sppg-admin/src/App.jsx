import { Routes, Route, Navigate } from 'react-router-dom';
import { useAuth } from './context/AuthContext';

import LoginAdmin from './pages/LoginAdmin';
import AdminLayout from './layouts/AdminLayout';
import Dashboard from './pages/Dashboard';
import DataGuru from './pages/DataGuru';
import VerifikasiPresensi from './pages/VerifikasiPresensi';
import KotakMasuk from './pages/KotakMasuk';
import ManajemenJadwal from './pages/ManajemenJadwal';

/**
 * ProtectedAdmin:
 *   - Belum login → /login
 *   - Login tapi bukan admin → /login
 *   - Ini mencegah user buka halaman admin via URL langsung.
 */
function ProtectedAdmin({ children }) {
  const { user } = useAuth();
  if (!user || user.role !== 'admin') return <Navigate to="/login" replace />;
  return children;
}

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginAdmin />} />

      {/* Semua halaman admin dibungkus AdminLayout (header + bottom nav) */}
      <Route element={
        <ProtectedAdmin>
          <AdminLayout />
        </ProtectedAdmin>
      }>
        <Route path="/dashboard"   element={<Dashboard />} />
        <Route path="/data-guru"   element={<DataGuru />} />
        <Route path="/verifikasi"  element={<VerifikasiPresensi />} />
        <Route path="/kotak-masuk" element={<KotakMasuk />} />
        <Route path="/jadwal"      element={<ManajemenJadwal />} />
      </Route>

      {/* Default redirect */}
      <Route path="/"  element={<Navigate to="/dashboard" replace />} />
      <Route path="*"  element={<Navigate to="/dashboard" replace />} />
    </Routes>
  );
}