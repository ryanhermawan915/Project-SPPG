/** @type {import('tailwindcss').Config} */
export default {
    content: ["./index.html", "./src/**/*.{js,jsx}"],
    theme: {
        extend: {
        colors: {
            primary: "#2563EB",
            surface: "#F8FAFC",
            card: "#FFFFFF",
            ink: "#0F172A",
            line: "#E2E8F0",
            success: "#16A34A",
            warning: "#D97706",
            notif: "#9333EA",
        },
        fontFamily: {
            sans: ["Inter", "ui-sans-serif", "system-ui", "sans-serif"],
        },
        borderRadius: { card: "14px" },
        boxShadow: {
            soft: "0 1px 2px rgba(15,23,42,.04), 0 4px 12px rgba(15,23,42,.04)",
        },
        },
    },
    plugins: [],
};