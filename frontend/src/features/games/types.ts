export interface GameType {
  id: string;
  name: string;
  date_time: string;
  home: string;
  guest: string;
}

export type LocalGameType = GameType & { removing: boolean; local_id: string };

export interface StatisticsType {
  game_id: string;
  home_points: number;
  guest_points: number;
}

export interface RemoveGameResponseType {
  message: string;
}

export interface ErrorResponseType {
  title: string;
  detail: string;
  status: number;
  class: string;
}
