import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter, HashRouter } from "react-router-dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import App from "./app/App.jsx";
import { AuthProvider } from "./hooks/useAuth.jsx";
import { ThemeProvider } from "./hooks/useTheme.jsx";
import { IS_STATIC } from "./services/mode";
import { seedStaticChat } from "./services/staticBackend";
import "./styles/index.css";

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      // Catalog data changes rarely: keep it fresh for longer so navigating
      // back to a page renders instantly from cache.
      staleTime: 5 * 60 * 1000,
      gcTime: 30 * 60 * 1000,
      retry: 1,
      refetchOnWindowFocus: false,
      refetchOnReconnect: false,
    },
  },
});

// GitHub Pages serves static files only, so the app switches to hash routing
// there (no server rewrites needed) and seeds the demo chat from the exported
// catalogue.
const Router = IS_STATIC ? HashRouter : BrowserRouter;

if (IS_STATIC) {
  seedStaticChat().catch(() => {});
}

createRoot(document.getElementById("root")).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <Router>
        <ThemeProvider>
          <AuthProvider>
            <App />
          </AuthProvider>
        </ThemeProvider>
      </Router>
    </QueryClientProvider>
  </StrictMode>
);
