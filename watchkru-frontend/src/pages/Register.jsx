import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useAuth } from "../hooks/useAuth";

const schema = z.object({
  name: z.string().min(2, "نام را وارد کنید"),
  username: z
    .string()
    .min(3, "حداقل ۳ کاراکتر")
    .regex(/^[a-zA-Z0-9_]+$/, "فقط حروف انگلیسی، عدد و _"),
  email: z.string().email("ایمیل معتبر وارد کنید"),
  password: z.string().min(8, "رمز عبور باید حداقل ۸ کاراکتر باشد"),
});

export default function Register() {
  const { register: registerUser } = useAuth();
  const navigate = useNavigate();
  const [serverError, setServerError] = useState("");

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm({ resolver: zodResolver(schema) });

  async function onSubmit(values) {
    setServerError("");
    try {
      await registerUser(values);
      navigate("/dashboard", { replace: true });
    } catch (err) {
      setServerError(err.response?.data?.message || "ثبت‌نام ناموفق بود.");
    }
  }

  return (
    <div className="mx-auto flex max-w-sm flex-col px-4 py-16 sm:px-6">
      <h1 className="font-display text-2xl text-ink">ساخت حساب کاربری</h1>
      <p className="mt-1 text-sm text-ink-dim">
        قبلاً ثبت‌نام کرده‌اید؟{" "}
        <Link to="/login" className="text-teal hover:underline">وارد شوید</Link>
      </p>

      <form onSubmit={handleSubmit(onSubmit)} className="mt-6 space-y-4">
        <div>
          <label className="mb-1 block text-sm text-ink-dim">نام</label>
          <input
            {...register("name")}
            className="w-full rounded-wk-sm border border-border bg-surface px-3 py-2.5 text-ink focus:border-gold/60"
          />
          {errors.name && <p className="mt-1 text-xs text-danger">{errors.name.message}</p>}
        </div>

        <div>
          <label className="mb-1 block text-sm text-ink-dim">نام کاربری</label>
          <input
            dir="ltr"
            {...register("username")}
            className="w-full rounded-wk-sm border border-border bg-surface px-3 py-2.5 text-ink focus:border-gold/60"
          />
          {errors.username && <p className="mt-1 text-xs text-danger">{errors.username.message}</p>}
        </div>

        <div>
          <label className="mb-1 block text-sm text-ink-dim">ایمیل</label>
          <input
            type="email"
            dir="ltr"
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
          {isSubmitting ? "در حال ساخت حساب…" : "ثبت‌نام"}
        </button>
      </form>
    </div>
  );
}
