export default function StatCard({ label, value, hint, Icon, tone = "primary" }) {
    const tones = {
        primary: "text-primary bg-blue-50",
        success: "text-success bg-green-50",
        warning: "text-warning bg-amber-50",
        notif: "text-notif bg-purple-50",
    };
    return (
        <div className="bg-card rounded-card border border-line p-5 shadow-soft">
        <div className="flex items-center justify-between">
            <p className="text-sm text-ink/60">{label}</p>
            {Icon && (
            <span className={`p-2 rounded-lg ${tones[tone]}`}>
                <Icon className="w-4 h-4" />
            </span>
            )}
        </div>
        <p className="mt-2 text-2xl font-bold">{value}</p>
        {hint && <p className="mt-1 text-xs text-ink/50">{hint}</p>}
        </div>
    );
}