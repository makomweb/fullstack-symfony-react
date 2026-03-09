import { BACKEND_API_URL } from "../../config/env";
import { type User } from "./UserContext";

interface ErrorResponseType {
  error: string;
}

export async function loginAsync(
  email: string,
  password: string,
  rememberMe: boolean,
): Promise<User> {
  const url = `${BACKEND_API_URL}/login`;
  const response = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    credentials: "include",
    body: JSON.stringify({
      email: email,
      password: password,
      _remember_me: rememberMe,
    }),
  });

  const obj = await response.json();

  if (!response.ok) {
    const errorObj = obj as ErrorResponseType;
    throw Error(errorObj.error);
  }

  return obj;
}

export async function logoutAsync(): Promise<void> {
  const url = `${BACKEND_API_URL}/logout`;
  const response = await fetch(url, {
    method: "POST",
    credentials: "include",
  });

  if (!response.ok) {
    throw Error(`Logout failed with ${response.status}!`);
  }
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
