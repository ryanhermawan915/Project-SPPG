import Button from './Button';

/**
 * Modal konfirmasi reusable untuk semua aksi berisiko:
 * logout, nonaktifkan akun, setujui/tolak izin, hapus jadwal.
 * Wajib menampilkan teks aksi yang jelas agar user tidak salah tekan.
 */
export default function Modal({
  open, title, message, onConfirm, onCancel,
  confirmText = 'Ya', cancelText = 'Batal', variant = 'primary', loading = false,
}) {
  if (!open) return null;
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
      <div className="bg-white rounded-2xl w-full max-w-sm p-5 space-y-4 animate-[fadeIn_0.15s_ease]">
        <h3 className="text-lg font-bold text-gray-900">{title}</h3>
        <p className="text-sm text-gray-600 leading-relaxed">{message}</p>
        <div className="flex gap-2">
          <Button variant="outline" onClick={onCancel} disabled={loading}>{cancelText}</Button>
          <Button variant={variant} onClick={onConfirm} loading={loading}>{confirmText}</Button>
        </div>
      </div>
    </div>
  );
}