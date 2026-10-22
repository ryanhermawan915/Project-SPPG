export default function DataTable({ columns, rows, onRowClick, emptyMessage = "Tidak ada data." }) {
    if (!rows.length) return <p className="py-8 text-center text-sm text-ink/50">{emptyMessage}</p>;
    return (
        <div className="overflow-x-auto -mx-1 px-1">
        <table className="w-full text-sm">
            <thead>
            <tr className="text-left text-xs uppercase text-ink/50 border-b border-line">
                {columns.map((c) => (
                <th key={c.key} className="py-3 px-3 font-medium whitespace-nowrap">{c.label}</th>
                ))}
            </tr>
            </thead>
            <tbody>
            {rows.map((row) => (
                <tr
                key={row.id}
                onClick={onRowClick ? () => onRowClick(row) : undefined}
                className={`border-b border-line/70 last:border-0 ${onRowClick ? "cursor-pointer hover:bg-surface" : ""}`}
                >
                {columns.map((c) => (
                    <td key={c.key} className="py-3 px-3 align-middle">
                    {c.render ? c.render(row) : row[c.key]}
                    </td>
                ))}
                </tr>
            ))}
            </tbody>
        </table>
        </div>
    );
}