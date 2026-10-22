export default function FormField({ label, htmlFor, error, hint, children }) {
    return (
        <div className="space-y-1.5">
        {label && <label htmlFor={htmlFor} className="block text-sm font-medium">{label}</label>}
        {children}
        {hint && !error && <p className="text-xs text-ink/50">{hint}</p>}
        {error && <p className="text-xs text-red-600">{error}</p>}
        </div>
    );
}