import { createContext } from "react";
import { LocalGameType, StatisticsType } from "./types";

type GameContextType = {
  game?: LocalGameType;
  incrementHome: () => void;
  incrementGuest: () => void;
  fetching: boolean;
  incrementing: boolean;
  removing: boolean;
  remove: () => void;
  statistics: StatisticsType;
};

const INITIAL_GAME_STATISTICS: StatisticsType = {
  game_id: "",
  home_points: 0,
  guest_points: 0,
};

const INITIAL_STATE: GameContextType = {
  game: undefined,
  incrementHome: () => {},
  incrementGuest: () => {},
  fetching: true,
  incrementing: false,
  removing: false,
  remove: () => {},
  statistics: INITIAL_GAME_STATISTICS,
};

export const GameContext = createContext(INITIAL_STATE);

export function withIncrement(
  statistics: StatisticsType,
  team: string,
): StatisticsType {
  switch (team) {
    case "home":
      return {
        game_id: statistics.game_id,
        home_points: statistics.home_points + 1,
        guest_points: statistics.guest_points,
      };

    case "guest":
      return {
        game_id: statistics.game_id,
        home_points: statistics.home_points,
        guest_points: statistics.guest_points + 1,
      };

    default:
      throw Error(`Unsupported team "${team}"!`);
  }
}
