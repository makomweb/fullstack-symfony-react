import { BACKEND_API_URL } from "../../config/env";
import { type User } from "./UserContext";

interface ErrorResponseType {
  error: string;
}

/**
 * Logout by redirecting to server logout endpoint.
 * Server clears session and redirects to login form.
 * Non-async but kept in auth API module for consistency.
 */
export function logoutAsync(): void {
  window.location.href = "/logout";
}

export async function checkRememberMeAsync(): Promise<User> {
  const url = `${BACKEND_API_URL}/me`;
  const response = await fetch(url, {
    method: "GET",
    credentials: "include",
  });

  const obj = await response.json();

  if (!response.ok) {
    const errorObj = obj as ErrorResponseType;
    throw Error(errorObj.error);
  }

  return obj;
}
