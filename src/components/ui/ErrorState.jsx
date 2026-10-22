import { AlertTriangle } from "lucide-react";

export default function ErrorState({ message = "Terjadi kesalahan saat memuat data.", onRetry }) {
    return (
        <div className="py-12 text-center text-sm">
        <AlertTriangle className="w-8 h-8 mx-auto text-warning" />
        <p className="mt-2 text-ink/70">{message}</p>
        {onRetry && (
            <button onClick={onRetry} className="mt-3 text-primary font-medium hover:underline">
            Coba lagi
            </button>
        )}
        </div>
    );
}