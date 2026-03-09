import { BACKEND_API_URL } from "../../config/env";
import { ErrorResponseType, GameType } from "./types";

export async function addGameAsync(
  home: string,
  guest: string,
  date_time: string,
): Promise<GameType> {
  const url = `${BACKEND_API_URL}/games`;
  const response = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify({ home: home, guest: guest, date_time: date_time }),
  });
  const json = await response.json();

  if (!response.ok) {
    const error = json as ErrorResponseType;
    throw Error(error.detail);
  }

  return {
    home: home,
    guest: guest,
    name: `${home} vs ${guest}`,
    id: json.game_id,
    date_time: json.date_time,
  };
}
