import "./styles/App.css";
import { MyErrorBoundary, CustomThemeProvider, Loading } from "./components";
import Router from "./Router";
import { Notifier, useRememberMe } from "./features";
import { UserContextProvider } from "./features/auth";

export default function App() {
  const { pending, me } = useRememberMe();
  return (
    <CustomThemeProvider>
      <Notifier>
        <MyErrorBoundary>
          {pending ? (
            <Loading text={"Logging in..."} />
          ) : (
            <UserContextProvider me={me!}>
              <Router />
            </UserContextProvider>
          )}
        </MyErrorBoundary>
      </Notifier>
    </CustomThemeProvider>
  );
}
