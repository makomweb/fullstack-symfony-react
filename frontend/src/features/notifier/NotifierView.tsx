import Snackbar from "@mui/material/Snackbar";
import Alert from "@mui/material/Alert";
import { useContext } from "react";
import { NotifierContext } from "./NotifierContext";

export default function NotifierView() {
  const { message, show } = useContext(NotifierContext);

  return (
    <Snackbar
      open={message !== null}
      anchorOrigin={{ vertical: "top", horizontal: "center" }}
    >
      <Alert severity="error" onClose={() => show(null)}>
        {message}
      </Alert>
    </Snackbar>
  );
}
