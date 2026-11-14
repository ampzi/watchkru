import axios from "axios";
import { IS_STATIC } from "./mode";
import { staticApi } from "./staticBackend";

const baseURL = import.meta.env.VITE_API_URL || "http://localhost:8787/api/v1";

const api = axios.create({
  baseURL,
  withCredentials: true,
  headers: {
    Accept: "application/json",
  },
});

// Laravel Sanctum SPA auth: fetch the CSRF cookie before any state-changing
// request when using the cookie-based flow. Token-based flow (Bearer) is
// used by default below; switch to withXsrfToken if you adopt the SPA flow.
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("wk_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (res) => res,
  (err) => {
    if (err.response?.status === 401) {
      localStorage.removeItem("wk_token");
    }
    return Promise.reject(err);
  }
);

// GitHub Pages has no PHP, so the static build answers the few direct api.*
// calls (dashboard tabs, admin counters, submission form) from the bundled
// catalogue instead.
export default IS_STATIC ? staticApi : api;
