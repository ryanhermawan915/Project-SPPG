// Placeholder integrasi Laravel. JANGAN taruh secret/API key di frontend.
const BASE_URL = import.meta.env.VITE_API_BASE_URL ?? "";

async function request(path, options = {}) {
    if (!BASE_URL) throw new Error("API base URL belum dikonfigurasi (placeholder).");
    const res = await fetch(`${BASE_URL}${path}`, {
        headers: { "Content-Type": "application/json", ...(options.headers || {}) },
        ...options,
    });
    if (!res.ok) throw new Error(`Request gagal: ${res.status}`);
    return res.json();
}

export const api = {
  // Nama/endpoint ini PLACEHOLDER — belum ditentukan PRD.
    login: (payload) => request("/login", { method: "POST", body: JSON.stringify(payload) }),
    logout: () => request("/logout", { method: "POST" }),
    me: () => request("/me"),
    getDashboard: () => request("/admin/dashboard"),
    getVerifikasi: () => request("/admin/verifikasi"),
    approveVerifikasi: (id, payload) => request(`/admin/verifikasi/${id}/approve`, { method: "POST", body: JSON.stringify(payload) }),
    rejectVerifikasi: (id, payload) => request(`/admin/verifikasi/${id}/reject`, { method: "POST", body: JSON.stringify(payload) }),
    getDataGuru: (params = "") => request(`/admin/guru${params}`),
    getInbox: () => request("/admin/inbox"),
    getJadwal: () => request("/admin/jadwal"),
    saveJadwal: (payload) => request("/admin/jadwal", { method: "PUT", body: JSON.stringify(payload) }),
};