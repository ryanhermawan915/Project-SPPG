export default function LoadingState({ label = "Memuat data…" }) {
    return (
        <div className="py-12 flex flex-col items-center gap-3 text-ink/60 text-sm">
        <div className="w-6 h-6 border-2 border-line border-t-primary rounded-full animate-spin" />
        {label}
        </div>
    );
}