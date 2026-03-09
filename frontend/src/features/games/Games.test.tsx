import { render, screen, waitFor } from "@testing-library/react";
import { describe, it, expect, vi, beforeAll, afterAll } from "vitest";
import * as api from "./api";
import Games from "./Games";

describe("App", () => {
  // Mock der fetch***Async Funktionen
  beforeAll(() => {
    vi.spyOn(api, "fetchGamesAsync").mockResolvedValue([
      {
        id: "ABC",
        name: "Mocked Game 1",
        date_time: "2024-12-22T11:30:03+00:00",
        home: "Union",
        guest: "Hertha",
      },
      {
        id: "XYZ",
        name: "Mocked Game 2",
        date_time: "2024-12-22T11:30:03+00:00",
        home: "HSV",
        guest: "St. Pauli",
      },
    ]);
    vi.spyOn(api, "fetchStatisticsAsync").mockResolvedValue({
      game_id:
        "2f435760b79beac45918ddf9adfa22c19127caf3b34588bc82360a96ce738e41",
      home_points: 2,
      guest_points: 0,
    });
  });

  afterAll(() => {
    vi.restoreAllMocks();
  });

  it("renders component", async () => {
    render(<Games />);

    // Warten auf das Abschließen aller Promises und State-Updates
    await waitFor(() => {
      expect(api.fetchGamesAsync).toHaveBeenCalledTimes(1);
      expect(api.fetchStatisticsAsync).toHaveBeenCalledTimes(2);
    });

    // Warten und prüfen, ob die gemockten Spiele angezeigt werden
    await screen.findByText("Union vs Hertha");
    await screen.findByText("HSV vs St. Pauli");
  });
});
