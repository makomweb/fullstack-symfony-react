import AppBar from "@mui/material/AppBar";
import IconButton from "@mui/material/IconButton";
import MenuIcon from "@mui/icons-material/Menu";
import LogoutIcon from "@mui/icons-material/Logout";
import Toolbar from "@mui/material/Toolbar";
import Typography from "@mui/material/Typography";
import { useState } from "react";
import { DrawerMenu, DRAWER_ITEMS } from "./index";
import { type User } from "../../features/auth";

type Props = {
  user: User;
  logout: () => void;
};

export default function NavBar({ user, logout }: Props) {
  const [drawerOpen, setDrawerOpen] = useState(false);

  const toggleDrawer = () => setDrawerOpen((prevState) => !prevState);

  return (
    <AppBar position={"fixed"}>
      <Toolbar>
        <IconButton
          size="large"
          edge="start"
          color="inherit"
          aria-label="menu"
          sx={{ mr: 2 }}
          onClick={toggleDrawer}
        >
          <MenuIcon />
        </IconButton>
        <Typography variant="h6" component="div" sx={{ flexGrow: 1 }}>
          Demo
        </Typography>
        <IconButton
          size="large"
          color="inherit"
          aria-label="logout"
          onClick={logout}
        >
          <LogoutIcon />
        </IconButton>
      </Toolbar>
      <DrawerMenu
        open={drawerOpen}
        user={user}
        items={DRAWER_ITEMS}
        onClose={toggleDrawer}
      />
    </AppBar>
  );
}
