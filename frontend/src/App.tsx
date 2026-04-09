import "./styles/App.css";
import { MyErrorBoundary, CustomThemeProvider, Loading } from "./components";
import Router from "./Router";
import { Notifier, useRememberMe } from "./features";
import { UserContextProvider } from "./features/auth";
import { BrowserRouter } from "react-router-dom";

export default function App() {
  const { pending, me } = useRememberMe();
  return (
    <CustomThemeProvider>
      <Notifier>
        <MyErrorBoundary>
          {pending ? (
            <Loading text={"Loading..."} />
          ) : (
            <BrowserRouter>
              <UserContextProvider me={me!}>
                <Router />
              </UserContextProvider>
            </BrowserRouter>
          )}
        </MyErrorBoundary>
      </Notifier>
    </CustomThemeProvider>
  );
}
