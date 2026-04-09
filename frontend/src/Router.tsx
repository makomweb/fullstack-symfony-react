import { Routes, Route, Navigate } from "react-router-dom";
import { NavBar, NotFound } from "./components";
import { styled } from "@mui/material";
import { Games, UserContext } from "./features";
import { useContext } from "react";

const Offset = styled("div")(({ theme }) => theme.mixins.toolbar);

export default function Router() {
  const { user, logout } = useContext(UserContext);

  if (!user) {
    return null;
  }

  return (
    <>
      <NavBar logout={logout} user={user} />
      <Offset />
      <Routes>
        <Route path={"/games"} element={<Games />} />
        <Route path={"/"} element={<Navigate to="/games" />} />
        <Route path={"*"} element={<NotFound />} />
      </Routes>
    </>
  );
}
