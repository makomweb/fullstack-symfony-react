import React, { useContext, useEffect, useReducer } from "react";
import { GamesContext } from "./GamesContext";
import { addGameAsync, fetchGamesAsync, removeGameAsync } from "./api";
import { NotifierContext } from "../notifier/NotifierContext";
import { DEFAULT_STATE, reduceGames } from "./reduceGames";
import { createGame } from "./createGame";
import * as LOGS_API from "@opentelemetry/api-logs";

type Props = {
  children: React.ReactNode;
};

export default function GamesContextProvider({ children }: Props) {
  const { show } = useContext(NotifierContext);
  const [state, dispatch] = useReducer(reduceGames, DEFAULT_STATE);
  const logger = LOGS_API.logs.getLogger("default");

  const fetch = () => {
    logger.emit({
      severityNumber: LOGS_API.SeverityNumber.INFO,
      severityText: "INFO",
      body: "📌 Fetch games",
    });
    dispatch({ type: "INITIATE_FETCH_GAMES" });
    fetchGamesAsync()
      .then((data) => dispatch({ type: "FETCH_GAMES_FINISHED", games: data }))
      .catch((ex: Error) => {
        dispatch({ type: "FETCH_GAMES_FAILED" });
        show(ex.message);
      });
  };

  const remove = (gameId: string) => {
    logger.emit({
      severityNumber: LOGS_API.SeverityNumber.INFO,
      severityText: "INFO",
      body: "♻️ Remove game",
    });
    dispatch({ type: "INITATE_GAME_REMOVAL", id: gameId });
    removeGameAsync(gameId)
      .then(() => dispatch({ type: "GAME_REMOVAL_FINISHED", id: gameId }))
      .catch((ex: Error) => {
        dispatch({ type: "GAME_REMOVAL_FAILED", id: gameId });
        show(ex.message);
      });
  };

  const addGame = () => {
    logger.emit({
      severityNumber: LOGS_API.SeverityNumber.INFO,
      severityText: "INFO",
      body: "🛠️ Add game",
    });
    const game = createGame();
    dispatch({ type: "ADD_GAME", game: game });
    addGameAsync(game.home, game.guest, game.date_time)
      .then((g) =>
        dispatch({ type: "GAME_ADDED", id: g.id, local_id: game.local_id }),
      )
      .catch((ex: Error) => {
        dispatch({ type: "ADD_GAME_FAILED", local_id: game.local_id });
        show(ex.message);
      });
  };

  useEffect(() => fetch(), []);

  return (
    <GamesContext.Provider
      value={{
        games: state.games,
        fetching: state.fetching,
        removeGame: remove,
        addRandomGame: addGame,
      }}
    >
      {children}
    </GamesContext.Provider>
  );
}
