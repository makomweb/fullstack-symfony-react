import { ReactNode } from "react";
import NotifierContextProvider from "./NotifierContextProvider";
import NotifierView from "./NotifierView";

type Props = {
  children: ReactNode;
};

export default function Notifier({ children }: Props) {
  return (
    <NotifierContextProvider>
      <NotifierView />
      {children}
    </NotifierContextProvider>
  );
}
