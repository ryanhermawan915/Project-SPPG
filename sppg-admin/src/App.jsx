import { RouterProvider } from "react-router-dom";
import { router } from "./router";
import ToastProvider from "./components/ToastProvider";
import AuthProvider from "./context/AuthContext";

export default function App() {
  return (
    <AuthProvider>
      <ToastProvider>
        <RouterProvider router={router} />
      </ToastProvider>
    </AuthProvider>
  );
}