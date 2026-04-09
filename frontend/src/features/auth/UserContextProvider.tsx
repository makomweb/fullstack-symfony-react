import React from "react";
import { User, UserContext } from "./UserContext";
import { NotifierContext } from "../notifier/NotifierContext";
import { logoutAsync } from "./api";

type Props = {
  children: React.ReactNode;
  me?: User;
};

export default function UserContextProvider({ children, me }: Props) {
  const [user] = React.useState<User | undefined>(me);
  const { show } = React.useContext(NotifierContext);

  const logout = () => {
    try {
      logoutAsync();
    } catch (ex: unknown) {
      const error = ex as Error;
      show(error.message);
    }
  };

  return (
    <UserContext.Provider
      value={{
        user: user,
        logout: logout,
      }}
    >
      {children}
    </UserContext.Provider>
  );
}
