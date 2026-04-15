import { BACKEND_API_URL } from "../../config/env";
import { ErrorResponseType, StatisticsType } from "./types";

export async function fetchStatisticsAsync(
  gameId: string,
): Promise<StatisticsType> {
  if (gameId === "") {
    throw Error("Invalid game ID!");
  }
  const urlPath = `${BACKEND_API_URL}/games/${gameId}`;
  const url = new URL(urlPath, window.location.origin);
  url.searchParams.append("from_cache", "yes");

  const response = await fetch(url, {
    method: "GET",
    credentials: "include",
  });
  const json = await response.json();

  if (!response.ok) {
    const error = json as ErrorResponseType;
    throw Error(error.detail);
  }

  return json;
}
