import { Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';

/**
 * Input dengan label & pesan error. Border berubah merah kalau ada error,
 * supaya user langsung tahu field mana yang salah.
 */
export default function Input({
  label, error, type = 'text', showPasswordToggle = false, className = '', ...props
}) {
  const [show, setShow] = useState(false);
  const inputType = showPasswordToggle ? (show ? 'text' : 'password') : type;

  return (
    <div className={`space-y-1.5 ${className}`}>
      {label && <label className="text-sm font-medium text-gray-700">{label}</label>}
      <div className="relative">
        <input
          type={inputType}
          className={`w-full h-11 px-3 rounded-xl border bg-white text-sm outline-none transition
            ${error ? 'border-danger focus:ring-2 focus:ring-red-200' : 'border-gray-300 focus:border-navy-500 focus:ring-2 focus:ring-navy-100'}`}
          {...props}
        />
        {showPasswordToggle && (
          <button
            type="button"
            onClick={() => setShow(!show)}
            className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500"
          >
            {show ? <EyeOff size={18} /> : <Eye size={18} />}
          </button>
        )}
      </div>
      {error && <p className="text-xs text-danger">{error}</p>}
    </div>
  );
}