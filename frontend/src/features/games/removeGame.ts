import { BACKEND_API_URL } from "../../config/env";
import { ErrorResponseType, RemoveGameResponseType } from "./types";

export async function removeGameAsync(
  gameId: string,
): Promise<RemoveGameResponseType> {
  const url = `${BACKEND_API_URL}/games/${gameId}`;
  const response = await fetch(url, {
    method: "DELETE",
    credentials: "include",
  });
  const json = await response.json();

  if (!response.ok) {
    const error = json as ErrorResponseType;
    throw Error(error.detail);
  }

  return json;
}
