import { useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { Eye, EyeOff, LockKeyhole, Mail, LogIn } from "lucide-react";
import { useAuth } from "../context/AuthContext";
import FormField from "../components/ui/FormField";

export default function Login() {
    const { login } = useAuth();
    const navigate = useNavigate();
    const location = useLocation();
    const from = location.state?.from?.pathname || "/";

    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [showPw, setShowPw] = useState(false);
    const [errors, setErrors] = useState({});
    const [serverError, setServerError] = useState("");
    const [submitting, setSubmitting] = useState(false);

    function validate() {
        const e = {};
        if (!email.trim()) e.email = "Email wajib diisi.";
        else if (!/^\S+@\S+\.\S+$/.test(email)) e.email = "Format email tidak valid.";
        if (!password) e.password = "Kata sandi wajib diisi.";
        setErrors(e);
        return Object.keys(e).length === 0;
    }

    async function onSubmit(ev) {
        ev.preventDefault();
        setServerError("");
        if (!validate()) return;
        setSubmitting(true);
        const res = await login(email.trim(), password);
        setSubmitting(false);
        if (res.ok) navigate(from, { replace: true });
        else setServerError(res.message || "Gagal masuk.");
    }

    return (
        <div className="min-h-screen flex items-center justify-center bg-surface p-4">
        <div className="w-full max-w-md">
            <div className="text-center mb-6">
            <p className="text-2xl font-bold text-primary">SPPG Admin</p>
            <p className="text-sm text-ink/60 mt-1">Sistem Pengelola Presensi Guru dan Karyawan</p>
            </div>

            <form onSubmit={onSubmit} className="bg-card rounded-card border border-line shadow-soft p-6 space-y-4" noValidate>
            <div>
                <h1 className="text-lg font-semibold">Masuk</h1>
                <p className="text-xs text-ink/50 mt-1">Gunakan akun admin yang telah diberikan.</p>
            </div>

            {serverError && (
                <div className="text-sm rounded-lg border border-red-300 bg-red-50 text-red-600 px-3 py-2">{serverError}</div>
            )}

            <FormField label="Email" htmlFor="email" error={errors.email}>
                <div className="relative">
                <Mail className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-ink/40" />
                <input
                    id="email"
                    type="email"
                    autoComplete="username"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className={`w-full pl-9 pr-3 py-2 rounded-lg border bg-card text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 ${errors.email ? "border-red-400" : "border-line"}`}
                    placeholder="admin@sekolah.sch.id"
                />
                </div>
            </FormField>

            <FormField label="Kata Sandi" htmlFor="password" error={errors.password}>
                <div className="relative">
                <LockKeyhole className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-ink/40" />
                <input
                    id="password"
                    type={showPw ? "text" : "password"}
                    autoComplete="current-password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className={`w-full pl-9 pr-10 py-2 rounded-lg border bg-card text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 ${errors.password ? "border-red-400" : "border-line"}`}
                    placeholder="••••••••"
                />
                <button type="button" onClick={() => setShowPw((v) => !v)} className="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-ink/50 hover:text-ink" aria-label={showPw ? "Sembunyikan kata sandi" : "Tampilkan kata sandi"}>
                    {showPw ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
                </div>
            </FormField>

            <button type="submit" disabled={submitting} className="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-medium hover:bg-blue-700 disabled:opacity-60">
                <LogIn className="w-4 h-4" />
                {submitting ? "Memproses…" : "Masuk"}
            </button>

            <p className="text-[11px] text-ink/40 text-center">
                Tidak ada registrasi. Akun admin dibuat di backend.
            </p>
            </form>
        </div>
        </div>
    );
}