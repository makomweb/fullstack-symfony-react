import React, { useState, useEffect, useContext } from "react";
import { GameContext, withIncrement } from "./GameContext";
import * as api from "./api";
import { NotifierContext } from "../notifier/NotifierContext";
import { LocalGameType } from "./types";
import * as LOGS_API from "@opentelemetry/api-logs";

type Props = {
  children: React.ReactNode;
  game: LocalGameType;
  removeGame: (gameId: string) => void;
};

export default function GameContextProvider({
  children,
  game,
  removeGame,
}: Props) {
  const { show } = useContext(NotifierContext);
  const [statistics, setStatistics] = useState({
    game_id: game.id,
    home_points: 0,
    guest_points: 0,
  });
  const [fetching, setFetching] = useState(true);
  const [incrementing, setIncrementing] = useState(false);
  const logger = LOGS_API.logs.getLogger("default");

  const increment = async (team: string) => {
    setIncrementing(true);
    logger.emit({
      severityNumber: LOGS_API.SeverityNumber.INFO,
      severityText: "INFO",
      body: `🎉 Increment score for ${team}`,
    });
    try {
      await api.incrementScoreAsync(game.id, team);
      setStatistics(withIncrement(statistics, team));
    } catch (ex: unknown) {
      const error = ex as Error;
      show(error.message);
    } finally {
      setIncrementing(false);
    }
  };

  const fetch = () => {
    // Prevent fetching statistics when this game
    // is not created in the backend yet:
    if (game.id === "") {
      return;
    }

    logger.emit({
      severityNumber: LOGS_API.SeverityNumber.INFO,
      severityText: "INFO",
      body: `⚙️ Fetch statistics`,
    });
    api
      .fetchStatisticsAsync(game.id)
      .then((stats) =>
        setStatistics({
          game_id: stats.game_id,
          home_points: stats.home_points,
          guest_points: stats.guest_points,
        }),
      )
      .catch((error: Error) => show(error.message))
      .finally(() => setFetching(false));
  };

  useEffect(() => fetch(), [game.id]);

  return (
    <GameContext.Provider
      value={{
        game: game,
        incrementHome: () => increment("home"),
        incrementGuest: () => increment("guest"),
        fetching: fetching,
        incrementing: incrementing,
        removing: game.removing,
        statistics: statistics,
        remove: () => removeGame(game.id),
      }}
    >
      {children}
    </GameContext.Provider>
  );
}
