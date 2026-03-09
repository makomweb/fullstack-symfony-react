import { createTheme } from "@mui/material/styles";
import { purple } from "@mui/material/colors";
import { ThemeProvider } from "@mui/material";

const CUSTOM_THEME = createTheme({
  palette: {
    primary: purple,
    secondary: purple,
  },
});

export default function CustomThemeProvider({
  children,
}: {
  children: React.ReactNode;
}) {
  return <ThemeProvider theme={CUSTOM_THEME}>{children}</ThemeProvider>;
}
