import { useContext } from "react";
import { GamesContext } from "./GamesContext";
import Game from "./Game";
import { Box, Fab, Grid2 } from "@mui/material";
import { NoData, Loading } from "../../components";
import AddIcon from "@mui/icons-material/Add";

export default function GamesView() {
  const { fetching, games, removeGame, addRandomGame } =
    useContext(GamesContext);

  if (fetching) {
    return <Loading text={"Fetching..."} />;
  }

  return (
    <>
      {games.length ? (
        <Box p={1}>
          <Grid2 container>
            {games.map((value, index) => (
              <Grid2 key={index} sx={{ pr: 1, pb: 1 }}>
                <Game game={value} key={index} removeGame={removeGame} />
              </Grid2>
            ))}
          </Grid2>
        </Box>
      ) : (
        <NoData text="No games" />
      )}
      <Fab
        color="primary"
        sx={{ position: "absolute", bottom: 20, right: 20 }}
        aria-label="add"
        onClick={addRandomGame}
      >
        <AddIcon />
      </Fab>
    </>
  );
}
