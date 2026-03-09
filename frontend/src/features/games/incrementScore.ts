import { BACKEND_API_URL } from "../../config/env";
import { ErrorResponseType } from "./types";

interface IncrementScoreResponseType {
  message: string;
}

export async function incrementScoreAsync(
  gameId: string,
  team: string,
): Promise<IncrementScoreResponseType> {
  const url = `${BACKEND_API_URL}/games/${gameId}`;
  const response = await fetch(url, {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify({ team: team, player_id: 1 }),
  });
  const json = await response.json();

  if (!response.ok) {
    const error = json as ErrorResponseType;
    throw Error(error.detail);
  }

  return json;
}
