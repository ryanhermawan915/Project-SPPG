import { createContext, useCallback, useContext, useState } from "react";
import Toast from "./ui/Toast";

const ToastCtx = createContext(null);
export const useToast = () => useContext(ToastCtx);

export default function ToastProvider({ children }) {
    const [toasts, setToasts] = useState([]);

    const push = useCallback((message, type = "success") => {
        const id = crypto.randomUUID();
        setToasts((t) => [...t, { id, message, type }]);
        setTimeout(() => setToasts((t) => t.filter((x) => x.id !== id)), 3500);
    }, []);

    return (
        <ToastCtx.Provider value={{ push }}>
        {children}
        <div className="fixed bottom-4 right-4 z-[100] flex flex-col gap-2">
            {toasts.map((t) => (
            <Toast key={t.id} {...t} onClose={() => setToasts((x) => x.filter((i) => i.id !== t.id))} />
            ))}
        </div>
        </ToastCtx.Provider>
    );
}