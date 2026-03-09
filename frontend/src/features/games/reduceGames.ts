import { GameType, LocalGameType } from "./types";
import { v4 as uuidv4 } from "uuid";

export type ActionType =
  | { type: "INITIATE_FETCH_GAMES" }
  | { type: "FETCH_GAMES_FINISHED"; games: GameType[] }
  | { type: "FETCH_GAMES_FAILED" }
  | { type: "INITATE_GAME_REMOVAL"; id: string }
  | { type: "GAME_REMOVAL_FINISHED"; id: string }
  | { type: "GAME_REMOVAL_FAILED"; id: string }
  | { type: "ADD_GAME"; game: LocalGameType }
  | { type: "GAME_ADDED"; id: string; local_id: string }
  | { type: "ADD_GAME_FAILED"; local_id: string };

export type StateType = {
  games: LocalGameType[];
  fetching: boolean;
};

export const DEFAULT_STATE: StateType = {
  games: [],
  fetching: false,
};

export function reduceGames(state: StateType, action: ActionType): StateType {
  const { type } = action;
  switch (type) {
    case "INITIATE_FETCH_GAMES":
      return {
        ...state,
        fetching: true,
      };

    case "FETCH_GAMES_FINISHED": {
      const { games: actionGames } = action;
      return {
        ...state,
        fetching: false,
        games: actionGames.map((value) => {
          return { ...value, removing: false, local_id: uuidv4() };
        }),
      };
    }

    case "FETCH_GAMES_FAILED": {
      return {
        ...state,
        fetching: false,
      };
    }

    case "INITATE_GAME_REMOVAL": {
      const { games: stateGames } = state;
      const { id: gameId } = action;
      return {
        ...state,
        games: stateGames.map((value) =>
          value.id === gameId ? { ...value, removing: true } : value,
        ),
      };
    }

    case "GAME_REMOVAL_FINISHED": {
      const { games: stateGames } = state;
      return {
        ...state,
        fetching: false,
        games: stateGames.filter((game) => game.id !== action.id),
      };
    }

    case "GAME_REMOVAL_FAILED": {
      const { games: stateGames } = state;
      const { id: gameId } = action;
      return {
        ...state,
        games: stateGames.map((value) =>
          value.id === gameId ? { ...value, removing: false } : value,
        ),
      };
    }

    case "ADD_GAME": {
      const { game } = action;
      return {
        ...state,
        games: [...state.games, game],
      };
    }

    case "GAME_ADDED": {
      const { id, local_id } = action;
      const { games: stateGames } = state;
      return {
        ...state,
        games: stateGames.map((value) =>
          value.local_id === local_id ? { ...value, id: id } : value,
        ),
      };
    }

    case "ADD_GAME_FAILED": {
      const { games: stateGames } = state;
      const { local_id } = action;
      return {
        ...state,
        fetching: false,
        games: stateGames.filter((game) => game.local_id !== local_id),
      };
    }

    default:
      return state;
  }
}
