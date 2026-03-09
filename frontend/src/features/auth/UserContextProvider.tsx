import React, { useContext, useState } from "react";
import { User, UserContext } from "./UserContext";
import { NotifierContext } from "../notifier/NotifierContext";
import { loginAsync, logoutAsync } from "./api";

type Props = {
  children: React.ReactNode;
  me?: User;
};

export default function UserContextProvider({ children, me }: Props) {
  const [user, setUser] = useState<User | undefined>(me);
  const [pending, setPending] = useState(false);
  const { show } = useContext(NotifierContext);

  const login = async (
    email: string,
    password: string,
    rememberMe: boolean,
  ) => {
    setPending(true);
    try {
      const user = await loginAsync(email, password, rememberMe);
      setUser(user);
    } catch (ex: unknown) {
      const error = ex as Error;
      show(error.message);
    } finally {
      setPending(false);
    }
  };

  const logout = () => {
    setPending(true);
    logoutAsync()
      .then(() => setUser(undefined))
      .catch((ex: Error) => show(ex.message))
      .finally(() => setPending(false));
  };

  return (
    <UserContext.Provider
      value={{
        user: user,
        pending: pending,
        loginAsync: login,
        logout: logout,
      }}
    >
      {children}
    </UserContext.Provider>
  );
}
