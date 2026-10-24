/**
 * Tombol dengan varian warna. Bikin sekali, pakai di mana-mana →
 * tinggi, radius, dan font konsisten di semua halaman.
 */
export default function Button({
  children, variant = 'primary', size = 'md', loading = false, className = '', ...props
}) {
  const base = 'w-full font-semibold rounded-xl transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2';

  const variants = {
    primary: 'bg-navy-500 text-white hover:bg-navy-700 shadow-sm',
    outline: 'border-2 border-navy-500 text-navy-500 hover:bg-navy-50',
    danger:  'bg-danger text-white hover:bg-red-600',
    success: 'bg-success text-white hover:bg-green-600',
    ghost:   'text-navy-500 hover:bg-navy-50',
  };

  const sizes = {
    sm: 'h-9 px-3 text-sm',
    md: 'h-11 px-4 text-sm',
    lg: 'h-12 px-6 text-base',
  };

  return (
    <button
      disabled={loading || props.disabled}
      className={`${base} ${variants[variant]} ${sizes[size]} ${className}`}
      {...props}
    >
      {loading && (
        <span className="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
      )}
      {children}
    </button>
  );
}