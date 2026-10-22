import { createContext, useContext, useEffect, useState } from "react";

const AuthCtx = createContext(null);
export const useAuth = () => useContext(AuthCtx);

// ⚠️ DEMO ONLY. Saat backend siap, ganti login() agar memanggil api.login
// dan simpan token/session sesuai mekanisme Laravel (Sanctum/JWT).
const STORAGE_KEY = "sppg_demo_auth";

export default function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [checking, setChecking] = useState(true);

    useEffect(() => {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (raw) {
        try {
            setUser(JSON.parse(raw));
        } catch {
            localStorage.removeItem(STORAGE_KEY);
        }
        }
        setChecking(false);
    }, []);

    async function login(email, password) {
        const { demoCredentials } = await import("../data/dummy");
        if (email === demoCredentials.email && password === demoCredentials.password) {
        const u = { name: demoCredentials.name, email, role: demoCredentials.role };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(u));
        setUser(u);
        return { ok: true };
        }
        return { ok: false, message: "Email atau kata sandi salah (demo)." };
    }

    function logout() {
        localStorage.removeItem(STORAGE_KEY);
        setUser(null);
    }

    return (
        <AuthCtx.Provider value={{ user, checking, login, logout }}>
        {children}
        </AuthCtx.Provider>
    );
}