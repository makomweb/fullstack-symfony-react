import { ReactNode, useState } from "react";
import { NotifierContext } from "./NotifierContext";

type Props = {
  children: ReactNode;
};

export default function NotifierContextProvider({ children }: Props) {
  const [message, setMessage] = useState<string | null>(null);

  return (
    <NotifierContext.Provider
      value={{
        message: message,
        show: (message) => setMessage(message),
      }}
    >
      {children}
    </NotifierContext.Provider>
  );
}
