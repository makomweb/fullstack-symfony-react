import { v4 as uuidv4 } from "uuid";
import { animals, colors, uniqueNamesGenerator } from "unique-names-generator";

const getShortName = () =>
  uniqueNamesGenerator({
    dictionaries: [colors, animals],
    separator: " ",
    style: "capital",
    length: 2,
  });

export function createGame() {
  const home = getShortName();
  const guest = getShortName();
  const date = new Date();
  return {
    home: home,
    guest: guest,
    date_time: date.toISOString(),
    name: `${home} vs ${guest}`,
    local_id: uuidv4(),
    id: "",
    removing: false,
  };
}
