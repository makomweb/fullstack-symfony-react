import { Box, Typography } from "@mui/material";

type Props = {
  text: string;
};

export default function Loading({ text }: Props) {
  return (
    <Box
      display="flex"
      justifyContent="center"
      alignItems="center"
      width="100%"
      height="100vh"
    >
      <Typography>{text}</Typography>
    </Box>
  );
}
