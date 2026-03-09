import { createContext } from "react";

export interface User {
  user: string;
}

type UserContextType = {
  user?: User;
  loginAsync: (
    email: string,
    password: string,
    rememberMe: boolean,
  ) => Promise<void>;
  logout: () => void;
  pending: boolean;
};

const INITIAL_STATE: UserContextType = {
  user: undefined,
  /* eslint-disable-next-line @typescript-eslint/no-unused-vars */
  loginAsync: (_email: string, _password: string, _rememberMe: boolean) =>
    Promise.resolve(),
  logout: () => {},
  pending: false,
};

export const UserContext = createContext(INITIAL_STATE);
