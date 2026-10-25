// Kartu putih dengan radius + shadow — dipakai untuk info, daftar, ringkasan
export default function Card({ children, className = '', onClick }) {
  return (
    <div
      onClick={onClick}
      className={`bg-white rounded-3xl shadow-sm border border-gray-100 p-6 ${
        onClick ? 'cursor-pointer active:scale-[0.99] transition' : ''
      } ${className}`}
    >
      {children}
    </div>
  );
}