import { createBrowserRouter, Navigate } from "react-router-dom";
import AdminLayout from "../components/layout/AdminLayout";
import ProtectedRoute from "../components/auth/ProtectedRoute";
import Login from "../pages/Login";
import Dashboard from "../pages/Dashboard";
import Verifikasi from "../pages/Verifikasi";
import DataGuru from "../pages/DataGuru";
import KotakMasuk from "../pages/KotakMasuk";
import Jadwal from "../pages/Jadwal";

export const router = createBrowserRouter([
    { path: "/login", element: <Login /> },
    {
        path: "/",
        element: (
        <ProtectedRoute>
            <AdminLayout />
        </ProtectedRoute>
        ),
        children: [
        { index: true, element: <Dashboard /> },
        { path: "verifikasi", element: <Verifikasi /> },
        { path: "data-guru", element: <DataGuru /> },
        { path: "kotak-masuk", element: <KotakMasuk /> },
        { path: "jadwal", element: <Jadwal /> },
        { path: "*", element: <Navigate to="/" replace /> },
        ],
    },
]);