import { lazy, Suspense } from "react";
import { Routes, Route } from "react-router-dom";
import Navbar from "../components/layout/Navbar";
import Footer from "../components/layout/Footer";
import RequireAuth from "../components/auth/RequireAuth";
import ScrollToTop from "../components/common/ScrollToTop";

const Home = lazy(() => import("../pages/Home"));
const Explore = lazy(() => import("../pages/Explore"));
const MediaDetails = lazy(() => import("../pages/MediaDetails"));
const CommunityDetails = lazy(() => import("../pages/CommunityDetails"));
const SearchPage = lazy(() => import("../pages/Search"));
const About = lazy(() => import("../pages/About"));
const Contact = lazy(() => import("../pages/Contact"));
const Chat = lazy(() => import("../pages/Chat"));
const Login = lazy(() => import("../pages/Login"));
const Register = lazy(() => import("../pages/Register"));
const Dashboard = lazy(() => import("../pages/Dashboard"));
const SubmitCommunity = lazy(() => import("../pages/SubmitCommunity"));
const AdminDashboard = lazy(() => import("../pages/admin/AdminDashboard"));
const NotFound = lazy(() => import("../pages/NotFound"));

function PageFallback() {
  return (
    <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6" aria-busy="true" aria-live="polite">
      <div className="h-7 w-48 animate-pulse rounded bg-surface-2/70" />
      <div className="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
        {Array.from({ length: 12 }).map((_, i) => (
          <div key={i} className="animate-pulse">
            <div className="aspect-[2/3] rounded-wk-md border border-border bg-surface-2/60" />
            <div className="mt-2 h-3 w-3/4 rounded bg-surface-2/60" />
          </div>
        ))}
      </div>
    </div>
  );
}

export default function App() {
  return (
    <div className="flex min-h-screen flex-col bg-bg">
      <ScrollToTop />
      <Navbar />
      <main className="flex-1">
        <Suspense fallback={<PageFallback />}>
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/explore" element={<Explore />} />
            <Route path="/media/:slug" element={<MediaDetails />} />
            <Route path="/communities/:slug" element={<CommunityDetails />} />
            <Route path="/search" element={<SearchPage />} />
            <Route path="/about" element={<About />} />
            <Route path="/contact" element={<Contact />} />
            <Route
              path="/chat"
              element={
                <RequireAuth>
                  <Chat />
                </RequireAuth>
              }
            />
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route
              path="/dashboard"
              element={
                <RequireAuth>
                  <Dashboard />
                </RequireAuth>
              }
            />
            <Route
              path="/submit-community"
              element={
                <RequireAuth>
                  <SubmitCommunity />
                </RequireAuth>
              }
            />
            <Route
              path="/admin/*"
              element={
                <RequireAuth roles={["admin", "super_admin", "moderator"]}>
                  <AdminDashboard />
                </RequireAuth>
              }
            />
            <Route path="*" element={<NotFound />} />
          </Routes>
        </Suspense>
      </main>
      <Footer />
    </div>
  );
}
