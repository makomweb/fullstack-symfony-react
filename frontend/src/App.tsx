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
      
      // Pass current URL as _target_path so user is redirected back after login
      // This uses Symfony's built-in post-login redirect mechanism
      const currentUrl = window.location.href;
      const targetPath = encodeURIComponent(currentUrl);
      
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
          <BrowserRouter>
            <AppContent />
          </BrowserRouter>
        </MyErrorBoundary>
      </Notifier>
    </CustomThemeProvider>
  );
}
