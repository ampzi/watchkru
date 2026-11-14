import api from "./api";
import { IS_STATIC } from "./mode";
import { staticServices } from "./staticBackend";

const realAuthService = {
  async login(payload) {
    const { data } = await api.post("/auth/login", payload);
    if (data?.data?.token) localStorage.setItem("wk_token", data.data.token);
    return data.data;
  },
  async register(payload) {
    const { data } = await api.post("/auth/register", payload);
    if (data?.data?.token) localStorage.setItem("wk_token", data.data.token);
    return data.data;
  },
  async logout() {
    try {
      await api.post("/auth/logout");
    } finally {
      localStorage.removeItem("wk_token");
    }
  },
  async me() {
    const { data } = await api.get("/auth/me");
    return data.data;
  },
};

const staticAuthService = {
  async login(payload) {
    const data = await staticServices.auth.login(payload);
    localStorage.setItem("wk_token", data.token);
    return data;
  },
  async register(payload) {
    const data = await staticServices.auth.register(payload);
    localStorage.setItem("wk_token", data.token);
    return data;
  },
  async logout() {
    await staticServices.auth.logout();
    localStorage.removeItem("wk_token");
  },
  async me() {
    return staticServices.auth.me();
  },
};

const realMediaService = {
  list(params) {
    return api.get("/media", { params }).then((r) => r.data);
  },
  get(slug) {
    return api.get(`/media/${slug}`).then((r) => r.data.data);
  },
  genres() {
    return api.get("/genres").then((r) => r.data.data);
  },
};

const realCommunityService = {
  list(params) {
    return api.get("/communities", { params }).then((r) => r.data);
  },
  get(slug) {
    return api.get(`/communities/${slug}`).then((r) => r.data.data);
  },
  submit(payload) {
    return api.post("/communities", payload).then((r) => r.data.data);
  },
  report(id, payload) {
    return api.post(`/communities/${id}/report`, payload).then((r) => r.data);
  },
  review(id, payload) {
    return api.post(`/communities/${id}/reviews`, payload).then((r) => r.data);
  },
};

const realSearchService = {
  search(q) {
    return api.get("/search", { params: { q } }).then((r) => r.data);
  },
};

export const authService = IS_STATIC ? staticAuthService : realAuthService;
export const mediaService = IS_STATIC ? staticServices.media : realMediaService;
export const communityService = IS_STATIC ? staticServices.community : realCommunityService;
export const searchService = IS_STATIC ? staticServices.search : realSearchService;
