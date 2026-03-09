import { useEffect, useState } from "react";
import { checkRememberMeAsync } from "./api";
import { type User } from "./UserContext";

export default function useRememberMe() {
  const [pending, setPending] = useState(true);
  const [me, setMe] = useState<User | undefined>(undefined);

  const checkAuth = async () => {
    try {
      setMe(await checkRememberMeAsync());
    } catch (ex: unknown) {
      console.error("Remember me request has failed!", ex);
    } finally {
      setPending(false);
    }
  };

  useEffect(() => {
    checkAuth();
  }, []);

  return { pending, me };
}
