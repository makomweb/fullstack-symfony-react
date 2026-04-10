import "./styles/App.css";
import { MyErrorBoundary, CustomThemeProvider, Loading } from "./components";
import Router from "./Router";
import { Notifier, useRememberMe } from "./features";
import { UserContextProvider } from "./features/auth";
import { BrowserRouter } from "react-router-dom";
import { useEffect } from "react";
import { BACKEND_API_URL } from "./config/env";

function AppContent() {
  const { pending, me } = useRememberMe();

  // Redirect to backend login if user is not authenticated
  useEffect(() => {
    if (!pending && !me) {
      // Extract backend base URL from API URL (remove /api suffix)
      const backendBaseUrl = BACKEND_API_URL.replace(/\/api$/, "");
      
      // Use relative SPA route as _target_path
      // In development: redirects back to backend's SPA route (same as production)
      // After login, user is redirected to /spa which serves the React app
      const targetPath = encodeURIComponent("/spa");
      
      window.location.href = `${backendBaseUrl}/login?_target_path=${targetPath}`;
    }
  }, [pending, me]);

  if (pending) {
    return <Loading text={"Loading..."} />;
  }

  if (!me) {
    // This should rarely happen due to the effect above, but guard against it
    return <Loading text={"Redirecting to login..."} />;
  }

  return (
    <UserContextProvider me={me}>
      <Router />
    </UserContextProvider>
  );
}

export default function App() {
  return (
    <CustomThemeProvider>
      <Notifier>
        <MyErrorBoundary>
          <BrowserRouter basename={window.location.pathname.startsWith('/spa') ? '/spa' : '/'}>
            <AppContent />
          </BrowserRouter>
        </MyErrorBoundary>
      </Notifier>
    </CustomThemeProvider>
  );
}
