import { Navigate, useLocation } from "react-router-dom";
import { useAuth } from "../../hooks/useAuth";

export default function RequireAuth({ children, roles }) {
  const { user, loading } = useAuth();
  const location = useLocation();

  if (loading) return null;

  if (!user) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  if (roles && !roles.includes(user.role)) {
    return (
      <div className="mx-auto max-w-xl px-4 py-24 text-center">
        <p className="text-ink">دسترسی لازم برای این بخش را ندارید.</p>
      </div>
    );
  }

  return children;
}
