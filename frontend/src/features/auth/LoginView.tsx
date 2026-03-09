import { useState } from "react";
import {
  TextField,
  Button,
  Box,
  styled,
  Divider,
  Checkbox,
  FormControlLabel,
} from "@mui/material";
import MuiCard from "@mui/material/Card";
import Stack from "@mui/material/Stack";
import { useNavigate, useLocation } from "react-router-dom";
import { Google } from "../../assets/icons/Google";
import { Facebook } from "../../assets/icons/Facebook";
import Flower from "../../assets/icons/Flower";

const Card = styled(MuiCard)(({ theme }) => ({
  display: "flex",
  flexDirection: "column",
  alignSelf: "center",
  width: "100%",
  padding: theme.spacing(4),
  gap: theme.spacing(2),
  margin: "auto",
  [theme.breakpoints.up("sm")]: {
    maxWidth: "340px",
  },
  boxShadow:
    "hsla(220, 30%, 5%, 0.05) 0px 5px 15px 0px, hsla(220, 25%, 10%, 0.05) 0px 15px 35px -5px",
  ...theme.applyStyles("dark", {
    boxShadow:
      "hsla(220, 30%, 5%, 0.5) 0px 5px 15px 0px, hsla(220, 25%, 10%, 0.08) 0px 15px 35px -5px",
  }),
}));

const LoginViewContainer = styled(Stack)(({ theme }) => ({
  height: "100dvh",
  width: "100vw", // Volle Breite
  overflow: "hidden", // Kein Scrollen
  position: "relative",
  padding: theme.spacing(2),
  boxSizing: "border-box",
  alignItems: "center", // Horizontal zentriert
  justifyContent: "center", // Vertikal zentriert
  [theme.breakpoints.up("sm")]: {
    padding: theme.spacing(4),
  },
  "&::before": {
    content: '""',
    display: "block",
    position: "absolute",
    zIndex: -1,
    inset: 0,
    backgroundImage:
      "radial-gradient(ellipse at 50% 50%, hsl(210, 100%, 97%), hsl(0, 0%, 100%))",
    backgroundRepeat: "no-repeat",
    ...theme.applyStyles("dark", {
      backgroundImage:
        "radial-gradient(at 50% 50%, hsla(210, 100%, 16%, 0.5), hsl(220, 30%, 5%))",
    }),
  },
}));

type Props = {
  loginAsync: (
    email: string,
    password: string,
    rememberMe: boolean,
  ) => Promise<void>;
  pending: boolean;
};

export default function LoginView({ loginAsync, pending }: Props) {
  const [email, setEmail] = useState<string | undefined>(undefined);
  const [password, setPassword] = useState<string | undefined>(undefined);
  const [rememberMe, setRememberMe] = useState(true);
  const navigate = useNavigate();
  const location = useLocation();
  const from = (location.state as { from?: Location })?.from?.pathname || "/";

  const toggleRememberMe = () => setRememberMe(!rememberMe);

  const doLogin = () => {
    loginAsync(email!, password!, rememberMe).then(() =>
      navigate(from, { replace: true }),
    );
  };

  const canSubmit =
    (email as unknown as boolean) && (password as unknown as boolean);

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    doLogin();
  };

  return (
    <LoginViewContainer direction="column">
      <Card variant="outlined">
        <Box sx={{ alignSelf: "center" }}>
          <Flower width={64} height={64} />
        </Box>
        <Box component="form" onSubmit={submit}>
          <TextField
            label="Email"
            type="email"
            fullWidth
            margin="normal"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
          <TextField
            label="Password"
            type="password"
            fullWidth
            margin="normal"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
          />
          <FormControlLabel
            control={
              <Checkbox checked={rememberMe} onChange={toggleRememberMe} />
            }
            label="Remember me"
          />
          <Button
            type="submit"
            variant="contained"
            color="primary"
            fullWidth
            sx={{ mt: 2 }}
            disabled={!canSubmit}
            loading={pending}
          >
            Login
          </Button>
        </Box>
        <Divider>or</Divider>
        <Box sx={{ display: "flex", flexDirection: "column", gap: 2 }}>
          <Button
            fullWidth
            variant="outlined"
            onClick={() => alert("Sign in with Google")}
            startIcon={<Google />}
          >
            Sign in with Google
          </Button>
          <Button
            fullWidth
            variant="outlined"
            onClick={() => alert("Sign in with Facebook")}
            startIcon={<Facebook />}
          >
            Sign in with Facebook
          </Button>
        </Box>
      </Card>
    </LoginViewContainer>
  );
}
