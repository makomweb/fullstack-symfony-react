import { useContext } from "react";
import {
  Box,
  Button,
  Card,
  CardActions,
  CardContent,
  CardHeader,
  Skeleton,
  Typography,
} from "@mui/material";
import IconButton from "@mui/material/IconButton";
import DeleteIcon from "@mui/icons-material/Delete";
import { GameContext } from "./GameContext";
import "./GameView.css";
import Avatar from "@mui/material/Avatar";
import { purple } from "@mui/material/colors";

export default function GameView() {
  const {
    game,
    incrementHome,
    incrementGuest,
    fetching,
    incrementing,
    removing,
    remove,
    statistics,
  } = useContext(GameContext);

  const title = `${game!.home} vs ${game!.guest}`;
  const avatar = `${Array.from(game!.home)[0]}${Array.from(game!.guest)[0]}`;
  const busy = fetching || removing || incrementing;

  return (
    <Card sx={{ minWidth: 275 }}>
      <CardHeader
        avatar={
          <Avatar sx={{ bgcolor: purple[500] }} aria-label="recipe">
            {avatar}
          </Avatar>
        }
        action={
          <IconButton aria-label="delete" onClick={remove} loading={busy}>
            <DeleteIcon />
          </IconButton>
        }
        title={title}
        subheader={new Date(game!.date_time).toLocaleDateString("en-US", {
          weekday: "long",
          year: "numeric",
          month: "long",
          day: "numeric",
        })}
      />
      <CardContent>
        <Box
          display="flex"
          justifyContent="center"
          width="100%"
          alignItems={"center"}
        >
          {fetching ? (
            <Skeleton variant="rectangular" width={60} height={40} />
          ) : (
            <Typography variant="h4">
              {statistics.home_points} : {statistics.guest_points}
            </Typography>
          )}
        </Box>
      </CardContent>
      <CardActions className="card__actions">
        <Button onClick={incrementHome} loading={busy} variant="outlined">
          Home
        </Button>
        <Button onClick={incrementGuest} loading={busy} variant="outlined">
          Guest
        </Button>
      </CardActions>
    </Card>
  );
}
