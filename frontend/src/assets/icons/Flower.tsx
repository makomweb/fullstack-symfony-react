import FlowerLogo from "./flower.svg";

type Props = {
  width: number;
  height: number;
};

export default function Flower({ width, height }: Props) {
  return (
    <img src={FlowerLogo} alt={"Flower logo"} width={width} height={height} />
  );
}
