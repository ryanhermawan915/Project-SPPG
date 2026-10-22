import { Inbox } from "lucide-react";

export default function EmptyState({ title = "Belum ada data", description = "Data akan muncul di sini setelah tersedia.", action }) {
    return (
        <div className="text-center py-12 px-4">
        <Inbox className="w-10 h-10 mx-auto text-ink/30" />
        <p className="mt-3 font-semibold">{title}</p>
        <p className="text-sm text-ink/60 mt-1">{description}</p>
        {action && <div className="mt-4">{action}</div>}
        </div>
    );
}