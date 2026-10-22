import { Search } from "lucide-react";

export default function SearchFilter({ value, onChange, placeholder = "Cari…", children }) {
    return (
        <div className="flex flex-col sm:flex-row gap-3 mb-4">
        <div className="relative flex-1">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-ink/40" />
            <input
            value={value}
            onChange={(e) => onChange(e.target.value)}
            placeholder={placeholder}
            className="w-full pl-9 pr-3 py-2 rounded-lg border border-line bg-card text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
            />
        </div>
        {children}
        </div>
    );
}