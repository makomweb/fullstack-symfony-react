import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { NavBar, NotFound } from "./components";
import { styled } from "@mui/material";
import { Games, UserContext, LoginView } from "./features";
import { useContext } from "react";

const Offset = styled("div")(({ theme }) => theme.mixins.toolbar);

export default function Router() {
  const { user, loginAsync, pending, logout } = useContext(UserContext);

  return (
    <BrowserRouter>
      {!user ? (
        <Routes>
          <Route
            path={"/login"}
            element={<LoginView loginAsync={loginAsync} pending={pending} />}
          />
          <Route path={"*"} element={<Navigate to="/login" />} />
        </Routes>
      ) : (
        <>
          <NavBar logout={logout} user={user} />
          <Offset />
          <Routes>
            <Route path={"/games"} element={<Games />} />
            <Route path={"/login"} element={<Navigate to="/games" />} />
            <Route path={"/"} element={<Navigate to="/games" />} />
            <Route path={"*"} element={<NotFound />} />
          </Routes>
        </>
      )}
    </BrowserRouter>
  );
}
