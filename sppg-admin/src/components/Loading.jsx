import { Inbox } from 'lucide-react';

export function Loading() {
  return (
    <div className="flex flex-col items-center justify-center py-12 gap-3">
      <div className="w-8 h-8 border-[3px] border-navy-100 border-t-navy-500 rounded-full animate-spin" />
      <p className="text-sm text-gray-500">Memuat data…</p>
    </div>
  );
}

export function EmptyState({
  title = 'Belum ada data',
  description = 'Data akan muncul di sini.',
}) {
  return (
    <div className="flex flex-col items-center justify-center py-12 text-center">
      <div className="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-3">
        <Inbox size={28} className="text-gray-400" />
      </div>
      <h4 className="font-semibold text-gray-700">{title}</h4>
      <p className="text-sm text-gray-500 mt-1 max-w-xs">{description}</p>
    </div>
  );
}