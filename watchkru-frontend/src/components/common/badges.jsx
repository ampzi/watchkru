// Status and taxonomy badges. Deliberately small, dot-led pills rather than
// icon-heavy chips — the dot carries the verification/activity signal so the
// label text stays short and scannable in Persian.

const DOT = {
  verified: "bg-teal",
  pending: "bg-gold",
  rejected: "bg-danger",
  needs_review: "bg-gold",
  active: "bg-teal",
  inactive: "bg-danger",
  unknown: "bg-ink-dim",
};

const LABEL = {
  verified: "تأییدشده",
  pending: "در انتظار بررسی",
  rejected: "ردشده",
  needs_review: "نیاز به بازبینی",
  active: "فعال",
  inactive: "غیرفعال",
  unknown: "نامشخص",
};

function Pill({ status, children }) {
  return (
    <span className="inline-flex items-center gap-1.5 rounded-full border border-border px-2.5 py-1 text-xs text-ink-dim">
      <span className={`h-1.5 w-1.5 rounded-full ${DOT[status] || "bg-ink-dim"}`} />
      {children}
    </span>
  );
}

export function VerificationBadge({ status }) {
  return <Pill status={status}>{LABEL[status] || status}</Pill>;
}

export function ActivityBadge({ status }) {
  return <Pill status={status}>{LABEL[status] || status}</Pill>;
}

export function GenreBadge({ children }) {
  return (
    <span className="inline-block rounded-wk-sm border border-border px-2 py-0.5 text-xs text-ink-dim">
      {children}
    </span>
  );
}

const PLATFORM_LABEL = {
  telegram_group: "گروه تلگرام",
  telegram_channel: "کانال تلگرام",
  discord_server: "سرور دیسکورد",
  instagram_page: "پیج اینستاگرام",
  reddit_community: "انجمن ردیت",
  whatsapp_group: "گروه واتس‌اپ",
  website_forum: "وب‌سایت / فروم",
  other: "سایر",
};

export function PlatformBadge({ platform }) {
  return (
    <span className="inline-flex items-center gap-1.5 rounded-wk-sm bg-surface-2 px-2.5 py-1 text-xs text-ink">
      {PLATFORM_LABEL[platform] || platform}
    </span>
  );
}
