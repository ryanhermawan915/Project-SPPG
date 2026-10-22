import { useState } from "react";
import { jadwalDefault } from "../data/dummy";
import FormField from "../components/ui/FormField";
import ConfirmDialog from "../components/ui/ConfirmDialog";
import { useToast } from "../components/ToastProvider";

const HARI = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];

export default function Jadwal() {
    const [form, setForm] = useState({ ...jadwalDefault });
    const [errors, setErrors] = useState({});
    const [confirm, setConfirm] = useState(false);
    const toast = useToast();

    function set(key, value) {
        setForm((f) => ({ ...f, [key]: value }));
    }

    function toggleHari(h) {
        setForm((f) => ({
        ...f,
        hariKerja: f.hariKerja.includes(h) ? f.hariKerja.filter((x) => x !== h) : [...f.hariKerja, h],
        }));
    }

    function validate() {
        const e = {};
        // ⚠️ Semua waktu wajib diisi sebelum disimpan. Aturan bisnis final di backend.
        if (!form.jamMasuk) e.jamMasuk = "Jam masuk wajib diisi.";
        if (!form.batasTerlambat) e.batasTerlambat = "Batas terlambat wajib diisi.";
        if (!form.jamPulang) e.jamPulang = "Jam pulang wajib diisi.";
        if (form.toleransiMenit === "" || Number(form.toleransiMenit) < 0) e.toleransiMenit = "Toleransi wajib berupa angka ≥ 0.";
        if (form.hariKerja.length === 0) e.hariKerja = "Pilih minimal satu hari kerja.";
        setErrors(e);
        return Object.keys(e).length === 0;
    }

    function onSubmit(ev) {
        ev.preventDefault();
        if (!validate()) return;
        setConfirm(true);
    }

    function doSave() {
        // ⚠️ SIMULASI — tidak tersimpan ke database.
        toast.push("Simulasi: jadwal disimpan (belum ke backend).", "success");
        setConfirm(false);
    }

    const inputCls = (err) => `w-full px-3 py-2 rounded-lg border bg-card text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 ${err ? "border-red-400" : "border-line"}`;

    return (
        <div className="space-y-4 max-w-2xl">
        <div>
            <h2 className="text-xl font-bold">Jadwal</h2>
            <p className="text-sm text-ink/60 mt-1">Pengaturan jadwal kerja. Nilai awal kosong = placeholder; aturan final ditentukan backend/PRD.</p>
        </div>

        <form onSubmit={onSubmit} className="bg-card rounded-card border border-line shadow-soft p-5 space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <FormField label="Jam Mulai Presensi Masuk" htmlFor="jamMasuk" error={errors.jamMasuk}>
                <input id="jamMasuk" type="time" value={form.jamMasuk} onChange={(e) => set("jamMasuk", e.target.value)} className={inputCls(errors.jamMasuk)} />
            </FormField>
            <FormField label="Batas Keterlambatan" htmlFor="batasTerlambat" error={errors.batasTerlambat}>
                <input id="batasTerlambat" type="time" value={form.batasTerlambat} onChange={(e) => set("batasTerlambat", e.target.value)} className={inputCls(errors.batasTerlambat)} />
            </FormField>
            <FormField label="Jam Mulai Presensi Pulang" htmlFor="jamPulang" error={errors.jamPulang}>
                <input id="jamPulang" type="time" value={form.jamPulang} onChange={(e) => set("jamPulang", e.target.value)} className={inputCls(errors.jamPulang)} />
            </FormField>
            <FormField label="Toleransi (menit)" htmlFor="toleransi" error={errors.toleransiMenit}>
                <input id="toleransi" type="number" min="0" value={form.toleransiMenit} onChange={(e) => set("toleransiMenit", e.target.value)} className={inputCls(errors.toleransiMenit)} placeholder="mis. 10" />
            </FormField>
            </div>

            <FormField label="Hari Kerja" error={errors.hariKerja}>
            <div className="flex flex-wrap gap-2">
                {HARI.map((h) => {
                const active = form.hariKerja.includes(h);
                return (
                    <button
                    type="button"
                    key={h}
                    onClick={() => toggleHari(h)}
                    className={`px-3 py-1.5 rounded-lg border text-sm ${active ? "bg-primary text-white border-primary" : "border-line hover:bg-surface"}`}
                    >
                    {h}
                    </button>
                );
                })}
            </div>
            </FormField>

            <div className="flex justify-end">
            <button type="submit" className="px-4 py-2 rounded-lg bg-primary text-white text-sm hover:bg-blue-700">Simpan</button>
            </div>
        </form>

        <ConfirmDialog
            open={confirm}
            title="Simpan Perubahan Jadwal?"
            confirmLabel="Simpan"
            onCancel={() => setConfirm(false)}
            onConfirm={doSave}
        >
            <p>Simulasi UI — perubahan belum dikirim ke backend. Backend yang menentukan validasi final.</p>
        </ConfirmDialog>
        </div>
    );
}