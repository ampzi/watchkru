// Static implementation of every service the UI uses. It ships with the
// GitHub Pages build: catalogue data comes from data/catalog.json (exported
// from MySQL) and user-generated state (login, chat, contact) lives in
// localStorage, because Pages cannot run PHP or a database.
import { asset } from "./mode";

const CATALOG_URL = `${import.meta.env.BASE_URL || "/"}data/catalog.json`;
const STORAGE_KEY = "wk_static_state";

let catalogPromise = null;

export function loadCatalog() {
  if (!catalogPromise) {
    catalogPromise = fetch(CATALOG_URL).then((r) => {
      if (!r.ok) throw new Error(`catalog ${r.status}`);
      return r.json();
    });
  }
  return catalogPromise;
}

/* ------------------------------------------------------------------ */
/* Persian/English normalisation (mirrors the backend search rules)   */
/* ------------------------------------------------------------------ */

const CHAR_MAP = {
  "ي": "ی", "ى": "ی", "ئ": "ی",
  "ك": "ک",
  "ة": "ه", "ۀ": "ه",
  "أ": "ا", "إ": "ا", "آ": "ا",
  "ؤ": "و",
  "۰": "0", "۱": "1", "۲": "2", "۳": "3", "۴": "4",
  "۵": "5", "۶": "6", "۷": "7", "۸": "8", "۹": "9",
  "٠": "0", "١": "1", "٢": "2", "٣": "3", "٤": "4",
  "٥": "5", "٦": "6", "٧": "7", "٨": "8", "٩": "9",
};

function normalize(value) {
  return String(value ?? "")
    .split("")
    .map((ch) => CHAR_MAP[ch] ?? ch)
    .join("")
    .replace(/[\u200c\u200d\u200e\u200f]/g, "")
    .replace(/\s+/g, " ")
    .toLowerCase()
    .trim();
}

function matches(haystack, needle) {
  return normalize(haystack).includes(needle);
}

/* ------------------------------------------------------------------ */
/* Catalogue shaping                                                  */
/* ------------------------------------------------------------------ */

function mapMedia(m) {
  return {
    ...m,
    poster_path: asset(m.poster_path),
    backdrop_path: asset(m.backdrop_path),
  };
}

function paginate(items, page = 1, perPage = 18) {
  const current = Math.max(1, Number(page) || 1);
  const last = Math.max(1, Math.ceil(items.length / perPage));
  const start = (current - 1) * perPage;

  return {
    data: items.slice(start, start + perPage),
    meta: {
      current_page: Math.min(current, last),
      last_page: last,
      per_page: perPage,
      total: items.length,
    },
  };
}

export const staticServices = {
  async authServiceMe() {
    const state = readState();
    return state.user || null;
  },

  auth: {
    async login({ email }) {
      const catalog = await loadCatalog();
      const found = catalog.users.find((u) => u.email.toLowerCase() === String(email).toLowerCase());
      if (!found) {
        const err = new Error("کاربر نمایشی با این ایمیل پیدا نشد.");
        err.response = { data: { message: "در نسخه نمایشی از ایمیل‌های نمونه استفاده کنید." } };
        throw err;
      }
      const user = { ...found, display_name: found.name };
      writeState({ ...readState(), user });
      return { user, token: "static-demo-token" };
    },
    async register({ username, email }) {
      const catalog = await loadCatalog();
      const user = {
        id: 1000 + catalog.users.length + 1,
        name: username || "کاربر مهمان",
        username: username || "guest",
        email,
        role: "user",
        display_name: username || "کاربر مهمان",
      };
      writeState({ ...readState(), user });
      return { user, token: "static-demo-token" };
    },
    async logout() {
      const state = readState();
      writeState({ ...state, user: null });
    },
    async me() {
      return readState().user || null;
    },
  },

  media: {
    async list(params = {}) {
      const catalog = await loadCatalog();
      const q = normalize(params.q || "");
      let items = catalog.media;

      if (params.type) items = items.filter((m) => m.media_type === params.type);
      if (params.genre) items = items.filter((m) => (m.genres || []).some((g) => g.slug === params.genre));
      if (params.year) items = items.filter((m) => String(m.release_year || "") === String(params.year));
      if (params.language) items = items.filter((m) => m.language === params.language);
      if (q) {
        items = items.filter(
          (m) =>
            matches(m.title, q) ||
            matches(m.original_title, q) ||
            (m.alternative_titles || []).some((t) => matches(t, q)) ||
            (m.cast || []).some((c) => matches(c, q)) ||
            (m.genres || []).some((g) => matches(g.name, q))
        );
      }

      const sorted = [...items];
      if (params.sort === "newest") {
        sorted.sort((a, b) => String(b.release_date || "").localeCompare(String(a.release_date || "")));
      } else {
        sorted.sort(
          (a, b) =>
            (b.communities_count || 0) - (a.communities_count || 0) ||
            String(b.release_date || "").localeCompare(String(a.release_date || ""))
        );
      }

      const page = paginate(sorted.map(mapMedia), params.page, params.per_page || 18);
      return page;
    },

    async get(slug) {
      const catalog = await loadCatalog();
      const media = catalog.media.find((m) => m.slug === slug);
      if (!media) throw new Error("not found");
      return mapMedia(media);
    },

    async genres() {
      const catalog = await loadCatalog();
      return catalog.genres;
    },
  },

  community: {
    async list(params = {}) {
      const catalog = await loadCatalog();
      const q = normalize(params.q || "");
      let items = catalog.communities;

      if (params.media) {
        items = items.filter(
          (c) => c.media?.slug === params.media || (c.media_slugs || []).includes(params.media)
        );
      }
      if (params.platform) items = items.filter((c) => c.platform === params.platform);
      if (params.language) items = items.filter((c) => c.language === params.language);
      if (params.verification_status) {
        items = items.filter((c) => c.verification_status === params.verification_status);
      }
      if (q) {
        items = items.filter((c) => matches(c.name, q) || matches(c.description, q) || matches(c.media?.title, q));
      }

      const sorted = [...items];
      if (params.sort === "newest") {
        sorted.sort((a, b) => String(b.created_at || "").localeCompare(String(a.created_at || "")));
      } else {
        sorted.sort(
          (a, b) =>
            (b.reviews_count || 0) - (a.reviews_count || 0) ||
            (b.member_count || 0) - (a.member_count || 0)
        );
      }

      return paginate(sorted, params.page, params.per_page || 20);
    },

    async get(slug) {
      const catalog = await loadCatalog();
      const community = catalog.communities.find((c) => c.slug === slug);
      if (!community) throw new Error("not found");
      return community;
    },

    async submit(payload) {
      const state = readState();
      writeState({ ...state, submissions: [...(state.submissions || []), { id: Date.now(), ...payload, status: "pending" }] });
      return { ...payload, status: "pending" };
    },

    async report(id, payload) {
      const state = readState();
      writeState({ ...state, reports: [...(state.reports || []), { id: Date.now(), community_id: id, ...payload }] });
      return { success: true };
    },

    async review() {
      return { success: true };
    },
  },

  search: {
    async search(q) {
      const catalog = await loadCatalog();
      const needle = normalize(q);
      if (needle.length < 2) return { media: [], communities: [], genres: [] };

      const media = catalog.media
        .filter(
          (m) =>
            matches(m.title, needle) ||
            matches(m.original_title, needle) ||
            (m.alternative_titles || []).some((t) => matches(t, needle)) ||
            matches(m.slug, needle) ||
            (m.cast || []).some((c) => matches(c, needle)) ||
            (m.genres || []).some((g) => matches(g.name, needle))
        )
        .sort((a, b) => {
          const aStarts = normalize(a.title).startsWith(needle) ? 0 : 1;
          const bStarts = normalize(b.title).startsWith(needle) ? 0 : 1;
          return aStarts - bStarts || (b.communities_count || 0) - (a.communities_count || 0);
        })
        .slice(0, 12)
        .map(mapMedia);

      const communities = catalog.communities
        .filter((c) => matches(c.name, needle) || matches(c.description, needle) || matches(c.media?.title, needle))
        .slice(0, 10);

      const genres = catalog.genres.filter((g) => matches(g.name, needle)).slice(0, 6);

      return { media, communities, genres };
    },
  },

  chat: {
    async groups() {
      const state = chatState();
      const userId = readState().user?.id;
      return state.groups.map((g) => {
        const messages = state.messages[g.id] || [];
        const last = messages[messages.length - 1];
        const lastRead = state.read[g.id] || 0;
        return {
          id: g.id,
          name: g.name,
          slug: g.slug,
          description: g.description,
          status: g.status,
          members_count: g.members_count ?? (g.member_ids || []).length,
          is_owner: g.created_by === userId,
          unread_count: messages.filter((m) => m.user_id !== userId && m.id > lastRead).length,
          last_message: last
            ? { body: last.body, created_at: last.created_at, user: last.user?.name }
            : null,
        };
      });
    },

    async createGroup(payload) {
      const state = chatState();
      const user = readState().user;
      const group = {
        id: Date.now(),
        name: payload.name,
        slug: `local-${Date.now()}`,
        description: payload.description,
        status: "pending",
        created_by: user?.id,
        member_ids: [user?.id],
        members_count: 1,
      };
      writeChat({ ...state, groups: [...state.groups, group] });
      return { success: true, data: group, message: "گروه ساخته شد و پس از تأیید مدیران نمایش داده می‌شود." };
    },

    async messages(groupId) {
      const state = chatState();
      const userId = readState().user?.id;
      return (state.messages[groupId] || []).map((m) => ({
        ...m,
        mine: m.user_id === userId,
      }));
    },

    async send(groupId, body) {
      const state = chatState();
      const user = readState().user;
      const message = {
        id: Date.now(),
        body,
        created_at: new Date().toISOString(),
        user: { id: user?.id, name: user?.display_name || user?.name, username: user?.username },
        user_id: user?.id,
      };
      const messages = { ...state.messages, [groupId]: [...(state.messages[groupId] || []), message] };
      writeChat({ ...state, messages });
      return { success: true, data: message };
    },

    async markRead(groupId) {
      const state = chatState();
      const messages = state.messages[groupId] || [];
      const lastId = messages.length ? messages[messages.length - 1].id : 0;
      writeChat({ ...state, read: { ...state.read, [groupId]: lastId } });
      return { success: true };
    },

    async members(groupId) {
      const state = chatState();
      const group = state.groups.find((g) => g.id === Number(groupId));
      return group?.members || [];
    },

    async requestDeletion(groupId) {
      const state = chatState();
      const groups = state.groups.map((g) =>
        g.id === Number(groupId) ? { ...g, status: "deletion_requested" } : g
      );
      writeChat({ ...state, groups });
      return { success: true, message: "درخواست حذف ثبت شد و پس از تأیید مدیران اعمال می‌شود." };
    },
  },

  notifications: {
    async list() {
      const state = readState();
      const items = state.notifications || [];
      return { items, unread_count: items.filter((n) => !n.read).length };
    },
    async markAllRead() {
      const state = readState();
      const items = (state.notifications || []).map((n) => ({ ...n, read: true }));
      writeState({ ...state, notifications: items });
      return { success: true };
    },
  },

  contact: {
    async send(payload) {
      const state = readState();
      writeState({
        ...state,
        contacts: [...(state.contacts || []), { id: Date.now(), ...payload, created_at: new Date().toISOString() }],
      });
      return { success: true, message: "پیام شما ثبت شد." };
    },
  },
};

/* ------------------------------------------------------------------ */
/* localStorage helpers                                               */
/* ------------------------------------------------------------------ */

function readState() {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY) || "{}");
  } catch {
    return {};
  }
}

function writeState(next) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
}

const CHAT_KEY = "wk_static_chat";

function chatState() {
  try {
    const raw = localStorage.getItem(CHAT_KEY);
    if (raw) return JSON.parse(raw);
  } catch {
    /* fall through to seed */
  }
  const seeded = { groups: [], messages: {}, read: {} };
  writeChat(seeded);
  return seeded;
}

function writeChat(state) {
  localStorage.setItem(CHAT_KEY, JSON.stringify(state));
}

/** Called once the catalogue is available: seeds the demo chat groups. */
export async function seedStaticChat() {
  const catalog = await loadCatalog();
  const existing = chatState();
  if (existing.groups.length) return;

  const groups = catalog.chat_groups
    .filter((g) => g.status === "approved" || g.status === "pending")
    .map((g) => ({
      id: g.id,
      name: g.name,
      slug: g.slug,
      description: g.description,
      status: g.status,
      created_by: g.created_by,
      members_count: g.members_count,
      members: g.members || [],
    }));

  const messages = {};
  catalog.chat_groups.forEach((g) => {
    messages[g.id] = (g.messages || []).map((m) => ({
      id: m.id,
      body: m.body,
      created_at: m.created_at,
      user: m.user,
      user_id: m.user_id,
    }));
  });

  writeChat({ groups, messages, read: {} });
}

/** Wrap a promise in an axios-like response so existing callers keep working. */
export function axiosLike(promise) {
  return promise.then((data) => ({ data: { success: true, data } }));
}

/* Routes used directly through `api` (Dashboard, admin tabs, submission form). */
export const staticApi = {
  async get(path) {
    const catalog = await loadCatalog();
    const state = readState();

    if (path === "/community-categories") return { data: { data: catalog.community_categories } };
    if (path === "/user/submissions") return { data: { data: state.submissions || [] } };
    if (path === "/user/reports") return { data: { data: state.reports || [] } };
    if (path === "/user/favorites") return { data: { data: [] } };
    if (path === "/admin/dashboard") return { data: { data: catalog.stats } };
    if (path.startsWith("/admin/communities")) return { data: { data: [] } };
    if (path.startsWith("/admin/reports")) return { data: { data: [] } };

    return { data: { data: [] } };
  },
  async post(path, body) {
    if (path === "/auth/login") return { data: { data: await staticServices.auth.login(body || {}) } };
    if (path === "/auth/register") return { data: { data: await staticServices.auth.register(body || {}) } };
    if (path === "/contact") return { data: await staticServices.contact.send(body || {}) };
    return { data: { success: true, data: body } };
  },
  async put() {
    return { data: { success: true } };
  },
  async delete() {
    return { data: { success: true } };
  },
};
