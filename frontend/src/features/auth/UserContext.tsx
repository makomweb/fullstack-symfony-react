import { createContext } from "react";

export interface User {
  user: string;
}

type UserContextType = {
  user?: User;
  logout: () => void;
};

const INITIAL_STATE: UserContextType = {
  user: undefined,
  logout: () => {},
};

export const UserContext = createContext(INITIAL_STATE);
