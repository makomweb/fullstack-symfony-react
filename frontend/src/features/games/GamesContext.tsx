import { createContext } from "react";
import { LocalGameType } from "./types";

type GamesContextType = {
  games: LocalGameType[];
  fetching: boolean;
  removeGame: (gameId: string) => void;
  addRandomGame: () => void;
};

const INITIAL_STATE: GamesContextType = {
  games: [],
  fetching: false,
  /* eslint-disable-next-line @typescript-eslint/no-unused-vars */
  removeGame: (_: string) => {},
  addRandomGame: () => {},
};

export const GamesContext = createContext(INITIAL_STATE);
