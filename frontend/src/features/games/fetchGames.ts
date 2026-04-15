import { BACKEND_API_URL } from "../../config/env";
import { ErrorResponseType, GameType } from "./types";

export async function fetchGamesAsync(): Promise<GameType[]> {
  const urlPath = `${BACKEND_API_URL}/games`;
  const url = new URL(urlPath, window.location.origin);
  url.searchParams.append("from_cache", "yes");

  const response = await fetch(url, { method: "GET", credentials: "include" });
  const json = await response.json();

  if (!response.ok) {
    const error = json as ErrorResponseType;
    throw Error(error.detail);
  }

  return json;
}
