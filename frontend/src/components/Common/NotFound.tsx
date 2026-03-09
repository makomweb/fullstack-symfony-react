import { Button, Stack, Typography } from "@mui/material";
import { NavLink } from "react-router-dom";

export default function NotFound() {
  return (
    <Stack
      display="flex"
      justifyContent="center"
      alignItems="center"
      width="100%"
      height="100vh"
    >
      <Typography>Not found</Typography>
      <NavLink to={"/games"}>
        <Button>Go back</Button>
      </NavLink>
    </Stack>
  );
}
