import {
  Box,
  Divider,
  Drawer,
  List,
  ListItem,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  Typography,
} from "@mui/material";
import { NavLink } from "react-router-dom";
import DrawerEntryType from "./DrawerItemType";
import { type User } from "../../features/auth";

type Props = {
  open: boolean;
  user: User;
  items: DrawerEntryType[];
  onClose: () => void;
};

export default function DrawerMenu({ open, user, items, onClose }: Props) {
  return (
    <Drawer
      anchor={"left"}
      variant={"temporary"}
      onClose={onClose}
      open={open}
      PaperProps={{
        sx: { minWidth: "240px" },
      }}
    >
      <Box onClick={onClose} sx={{ textAlign: "center" }}>
        <Typography variant="h6" sx={{ my: 2 }}>
          {user.user}
        </Typography>
        <Divider />
        <List>
          {items.map((entry) => (
            <ListItem key={entry.text} disablePadding>
              <ListItemButton component={NavLink} to={`/${entry.url}`}>
                <ListItemIcon>{entry.icon}</ListItemIcon>
                <ListItemText primary={entry.text} />
              </ListItemButton>
            </ListItem>
          ))}
        </List>
      </Box>
    </Drawer>
  );
}
