import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useQuery } from "@tanstack/react-query";
import api from "../services/api";
import { communityService, mediaService } from "../services/authService";

const schema = z.object({
  name: z.string().min(2, "نام انجمن را وارد کنید"),
  platform: z.string().min(1, "پلتفرم را انتخاب کنید"),
  external_url: z.string().url("آدرس معتبر وارد کنید"),
  media_id: z.string().min(1, "فیلم یا سریال را انتخاب کنید"),
  community_category_id: z.string().min(1, "دسته‌بندی را انتخاب کنید"),
  language: z.string().min(1),
  description: z.string().max(1000).optional(),
  member_count: z.string().optional(),
});

export default function SubmitCommunity() {
  const navigate = useNavigate();
  const [serverError, setServerError] = useState("");
  const [done, setDone] = useState(false);

  const { data: mediaOptions } = useQuery({
    queryKey: ["media", "options"],
    queryFn: () => mediaService.list({ per_page: 100 }).then((r) => r.data),
  });
  const { data: categories } = useQuery({
    queryKey: ["community-categories"],
    queryFn: () => api.get("/community-categories").then((r) => r.data.data),
  });

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm({ resolver: zodResolver(schema), defaultValues: { language: "fa" } });

  async function onSubmit(values) {
    setServerError("");
    try {
      await communityService.submit(values);
      setDone(true);
    } catch (err) {
      setServerError(err.response?.data?.message || "ارسال ناموفق بود.");
    }
  }

  if (done) {
    return (
      <div className="mx-auto max-w-lg px-4 py-20 text-center sm:px-6">
        <h1 className="font-display text-2xl text-ink">انجمن شما ثبت شد</h1>
        <p className="mt-2 text-ink-dim">
          پس از بررسی توسط تیم واچ‌کرو، انجمن شما در صفحه عنوان مربوطه نمایش داده خواهد شد. می‌توانید وضعیت آن را
          از داشبورد خود پیگیری کنید.
        </p>
        <button
          onClick={() => navigate("/dashboard")}
          className="mt-6 rounded-wk-sm bg-gold px-5 py-2.5 text-sm font-medium text-bg hover:bg-gold/90"
        >
          رفتن به داشبورد
        </button>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-lg px-4 py-10 sm:px-6">
      <h1 className="font-display text-2xl text-ink">معرفی انجمن شما</h1>
      <p className="mt-1 text-sm text-ink-dim">
        اطلاعات را کامل وارد کنید. انجمن شما قبل از انتشار توسط تیم واچ‌کرو بررسی می‌شود.
      </p>

      <form onSubmit={handleSubmit(onSubmit)} className="mt-6 space-y-4">
        <div>
          <label className="mb-1 block text-sm text-ink-dim">نام انجمن</label>
          <input
            {...register("name")}
            className="w-full rounded-wk-sm border border-border bg-surface px-3 py-2.5 text-ink focus:border-gold/60"
          />
          {errors.name && <p className="mt-1 text-xs text-danger">{errors.name.message}</p>}
        </div>

        <div className="grid grid-cols-2 gap-3">
          <div>
            <label className="mb-1 block text-sm text-ink-dim">پلتفرم</label>
            <select
              {...register("platform")}
              className="w-full rounded-wk-sm border border-border bg-surface px-3 py-2.5 text-ink"
            >
              <option value="">انتخاب کنید</option>
              <option value="telegram_group">گروه تلگرام</option>
              <option value="telegram_channel">کانال تلگرام</option>
              <option value="discord_server">سرور دیسکورد</option>
              <option value="instagram_page">پیج اینستاگرام</option>
              <option value="reddit_community">انجمن ردیت</option>
              <option value="whatsapp_group">گروه واتس‌اپ</option>
              <option value="website_forum">وب‌سایت / فروم</option>
              <option value="other">سایر</option>
            </select>
            {errors.platform && <p className="mt-1 text-xs text-danger">{errors.platform.message}</p>}
          </div>

          <div>
            <label className="mb-1 block text-sm text-ink-dim">دسته‌بندی</label>
            <select
              {...register("community_category_id")}
              className="w-full rounded-wk-sm border border-border bg-surface px-3 py-2.5 text-ink"
            >
              <option value="">انتخاب کنید</option>
              {categories?.map((c) => (
                <option key={c.id} value={c.id}>{c.name}</option>
              ))}
            </select>
            {errors.community_category_id && (
              <p className="mt-1 text-xs text-danger">{errors.community_category_id.message}</p>
            )}
          </div>
        </div>

        <div>
          <label className="mb-1 block text-sm text-ink-dim">فیلم یا سریال مرتبط</label>
          <select
            {...register("media_id")}
            className="w-full rounded-wk-sm border border-border bg-surface px-3 py-2.5 text-ink"
          >
            <option value="">انتخاب کنید</option>
            {mediaOptions?.map((m) => (
              <option key={m.id} value={m.id}>{m.title} ({m.release_year})</option>
            ))}
          </select>
          {errors.media_id && <p className="mt-1 text-xs text-danger">{errors.media_id.message}</p>}
        </div>

        <div>
          <label className="mb-1 block text-sm text-ink-dim">لینک انجمن</label>
          <input
            dir="ltr"
            {...register("external_url")}
            placeholder="https://t.me/..."
            className="w-full rounded-wk-sm border border-border bg-surface px-3 py-2.5 text-ink placeholder:text-ink-dim"
          />
          {errors.external_url && <p className="mt-1 text-xs text-danger">{errors.external_url.message}</p>}
        </div>

        <div>
          <label className="mb-1 block text-sm text-ink-dim">تعداد اعضا (اختیاری)</label>
          <input
            type="number"
            {...register("member_count")}
            className="w-full rounded-wk-sm border border-border bg-surface px-3 py-2.5 text-ink"
          />
        </div>

        <div>
          <label className="mb-1 block text-sm text-ink-dim">توضیحات</label>
          <textarea
            rows={4}
            {...register("description")}
            className="w-full rounded-wk-sm border border-border bg-surface px-3 py-2.5 text-ink"
          />
        </div>

        {serverError && <p className="text-sm text-danger">{serverError}</p>}

        <button
          type="submit"
          disabled={isSubmitting}
          className="w-full rounded-wk-sm bg-gold py-2.5 text-sm font-medium text-bg hover:bg-gold/90 disabled:opacity-50"
        >
          {isSubmitting ? "در حال ارسال…" : "ارسال برای بررسی"}
        </button>
      </form>
    </div>
  );
}
