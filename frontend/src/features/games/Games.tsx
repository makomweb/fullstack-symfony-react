import GamesContextProvider from "./GamesContextProvider";
import GamesView from "./GamesView";

export default function Games() {
  return (
    <GamesContextProvider>
      <GamesView />
    </GamesContextProvider>
  );
}
