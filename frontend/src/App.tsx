import "./styles/App.css";
import { MyErrorBoundary, CustomThemeProvider, Loading } from "./components";
import Router from "./Router";
import { Notifier, useRememberMe } from "./features";
import { UserContextProvider } from "./features/auth";
import { BrowserRouter, useNavigate } from "react-router-dom";
import { useEffect } from "react";

function AppContent() {
  const { pending, me } = useRememberMe();
  const navigate = useNavigate();

  // Redirect to login if user is not authenticated (fallback defense-in-depth)
  useEffect(() => {
    if (!pending && !me) {
      navigate("/login", { replace: true });
    }
  }, [pending, me, navigate]);

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
