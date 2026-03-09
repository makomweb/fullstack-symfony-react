import { createContext } from "react";

export interface NotifierType {
  message: string | null;
  show: (message: string | null) => void;
}

const INITIAL_STATE: NotifierType = {
  message: null,
  /* eslint-disable-next-line @typescript-eslint/no-unused-vars */
  show: (_: string | null) => {},
};

export const NotifierContext = createContext(INITIAL_STATE);
