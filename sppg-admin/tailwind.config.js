/** @type {import('tailwindcss').Config} */
export default {
  content: ["./index.html", "./src/**/*.{js,jsx}"],
  theme: {
    extend: {
      colors: {
        // Warna utama: navy profesional (sesuai laporan & Figma)
        navy: {
          50:  '#eef3fb',
          100: '#d7e2f5',
          500: '#2563eb', // biru tombol (Figma)
          700: '#1e40af',
          900: '#0f2557',
        },
        // Status
        success: '#22c55e',  // hijau: hadir / berhasil
        warning: '#f59e0b',  // oranye: terlambat / menunggu
        danger:  '#ef4444',  // merah: ditolak / alpha
        pageBg:  '#f6f8fb',  // background abu-abu sangat muda
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      borderRadius: {
        xl: '14px',
        '2xl': '18px',
      },
    },
  },
  plugins: [],
};