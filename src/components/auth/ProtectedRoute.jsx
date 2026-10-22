import { Navigate, useLocation } from "react-router-dom";
import { useAuth } from "../../context/AuthContext";

export default function ProtectedRoute({ children }) {
    const { user, checking } = useAuth();
    const location = useLocation();

    if (checking) {
        return <div className="min-h-screen flex items-center justify-center text-sm text-ink/60">Memeriksa sesi…</div>;
    }
    if (!user) return <Navigate to="/login" replace state={{ from: location }} />;
    return children;
}