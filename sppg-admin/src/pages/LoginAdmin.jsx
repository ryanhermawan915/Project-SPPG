import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import Button from '../components/Button';
import Input from '../components/Input';
import { ShieldCheck, AlertCircle } from 'lucide-react';

export default function LoginAdmin() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  const { login } = useAuth();
  const navigate = useNavigate();

  const validate = () => {
    const e = {};
    if (!email) e.email = 'Email wajib diisi';
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) e.email = 'Format email tidak valid';
    if (!password) e.password = 'Password wajib diisi';
    else if (password.length < 6) e.password = 'Password minimal 6 karakter';
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const handleSubmit = async (ev) => {
    ev.preventDefault();
    if (!validate()) return;
    setLoading(true);
    await new Promise((r) => setTimeout(r, 800));
    if (email === 'admin@sekolah.sch.id' && password === 'admin123') {
      login({ name: 'Admin Sekolah', role: 'admin', email });
      navigate('/dashboard', { replace: true });
    } else {
      setErrors({ form: 'Email atau password salah. Silakan coba lagi.' });
    }
    setLoading(false);
  };

  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-navy-700 via-navy-500 to-navy-700 relative overflow-hidden">
      {/* Dekorasi titik */}
      <div
        className="absolute inset-0 opacity-10"
        style={{ backgroundImage: 'radial-gradient(circle, #fff 1px, transparent 1px)', backgroundSize: '24px 24px' }}
      />

      <div className="relative w-full max-w-md">
        <div className="text-center mb-6">
          <div className="inline-flex items-center gap-2 mb-2">
            <ShieldCheck size={28} className="text-white" />
            <span className="text-white font-extrabold text-2xl">SPPG Admin</span>
          </div>
          <p className="text-white/70 text-sm">Sistem Pengelola Presensi Guru & Karyawan</p>
        </div>

        <div className="bg-white rounded-2xl shadow-2xl p-8">
          <h2 className="text-2xl font-bold text-gray-900 mb-1">Masuk Admin</h2>
          <p className="text-sm text-gray-500 mb-6">Khusus administrator sekolah.</p>

          {errors.form && (
            <div className="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700 flex items-start gap-2">
              <AlertCircle size={18} className="shrink-0 mt-0.5" />
              <span>{errors.form}</span>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4" noValidate>
            <Input
              label="Email"
              type="email"
              placeholder="admin@sekolah.sch.id"
              value={email}
              onChange={(e) => { setEmail(e.target.value); setErrors({ ...errors, email: undefined, form: undefined }); }}
              error={errors.email}
            />
            <Input
              label="Password"
              type="password"
              placeholder="••••••••"
              showPasswordToggle
              value={password}
              onChange={(e) => { setPassword(e.target.value); setErrors({ ...errors, password: undefined, form: undefined }); }}
              error={errors.password}
            />
            <Button type="submit" size="lg" loading={loading}>
              Masuk
            </Button>
          </form>

          <div className="mt-6 p-3 rounded-xl bg-navy-50 border border-navy-100 text-xs text-navy-700">
            <p className="font-semibold mb-1">Kredensial demo:</p>
            <p>Email: <code className="font-mono">admin@sekolah.sch.id</code></p>
            <p>Password: <code className="font-mono">admin123</code></p>
          </div>
        </div>
      </div>
    </div>
  );
}