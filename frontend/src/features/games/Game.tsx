import { LocalGameType } from "./types";
import GameContextProvider from "./GameContextProvider";
import GameView from "./GameView";

type PropsType = {
  game: LocalGameType;
  removeGame: (gameId: string) => void;
};

export default function Game({ game, removeGame }: PropsType) {
  return (
    <GameContextProvider game={game} removeGame={removeGame}>
      <GameView />
    </GameContextProvider>
  );
}
