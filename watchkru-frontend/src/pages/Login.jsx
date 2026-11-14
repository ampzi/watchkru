import { useState } from "react";
import { Link, useNavigate, useLocation } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useAuth } from "../hooks/useAuth";
import { IS_STATIC } from "../services/mode";

const schema = z.object({
  email: z.string().email("ایمیل معتبر وارد کنید"),
  password: z.string().min(8, "رمز عبور باید حداقل ۸ کاراکتر باشد"),
});

export default function Login() {
  const { login } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [serverError, setServerError] = useState("");

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm({ resolver: zodResolver(schema) });

  async function onSubmit(values) {
    setServerError("");
    try {
      await login(values);
      navigate(location.state?.from?.pathname || "/dashboard", { replace: true });
    } catch (err) {
      setServerError(err.response?.data?.message || "ورود ناموفق بود.");
    }
  }

  return (
    <div className="mx-auto flex max-w-sm flex-col px-4 py-16 sm:px-6">
      <h1 className="font-display text-2xl text-ink">ورود به واچ‌کرو</h1>
      <p className="mt-1 text-sm text-ink-dim">هنوز حساب ندارید؟{" "}
        <Link to="/register" className="text-teal hover:underline">ثبت‌نام کنید</Link>
      </p>

      {IS_STATIC && (
        <p className="mt-4 rounded-wk-sm border border-border bg-surface p-3 text-xs leading-6 text-ink-dim">
          نسخه نمایشی روی GitHub Pages: با یکی از ایمیل‌های نمونه (مثلاً{" "}
          <span className="lat">admin@watchkru.test</span>) و رمز{" "}
          <span className="lat">password</span> وارد شوید. داده‌های شما فقط در همین مرورگر ذخیره می‌شود.
        </p>
      )}

      <form onSubmit={handleSubmit(onSubmit)} className="mt-6 space-y-4">
        <div>
          <label className="mb-1 block text-sm text-ink-dim">ایمیل</label>
          <input
            type="email"
            {...register("email")}
            className="w-full rounded-wk-sm border border-border bg-surface px-3 py-2.5 text-ink focus:border-gold/60"
          />
          {errors.email && <p className="mt-1 text-xs text-danger">{errors.email.message}</p>}
        </div>

        <div>
          <label className="mb-1 block text-sm text-ink-dim">رمز عبور</label>
          <input
            type="password"
            {...register("password")}
            className="w-full rounded-wk-sm border border-border bg-surface px-3 py-2.5 text-ink focus:border-gold/60"
          />
          {errors.password && <p className="mt-1 text-xs text-danger">{errors.password.message}</p>}
        </div>

        {serverError && <p className="text-sm text-danger">{serverError}</p>}

        <button
          type="submit"
          disabled={isSubmitting}
          className="w-full rounded-wk-sm bg-gold py-2.5 text-sm font-medium text-bg hover:bg-gold/90 disabled:opacity-50"
        >
          {isSubmitting ? "در حال ورود…" : "ورود"}
        </button>
      </form>
    </div>
  );
}
