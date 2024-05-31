import TradeGoodTypes from "@enums/tradeGoodTypes";

const SupplyLevels = Object.freeze({
  SCARCE: "SCARCE",
  LIMITED: "LIMITED",
  MODERATE: "MODERATE",
  HIGH: "HIGH",
  ABUNDANT: "ABUNDANT",
});

export default SupplyLevels;

function getSupplyColorAscending (supply)  {
  switch (supply) {
    case SupplyLevels.ABUNDANT:
      return "green";
    case SupplyLevels.HIGH:
      return "lime";
    case SupplyLevels.MODERATE:
      return "yellow";
    case SupplyLevels.LIMITED:
      return "orange";
    case SupplyLevels.SCARCE:
      return "red";
    default:
      return "black";
  }
}

function getSupplyColorDescending(supply) {
  switch (supply) {
    case SupplyLevels.ABUNDANT:
      return "red";
    case SupplyLevels.HIGH:
      return "orange";
    case SupplyLevels.MODERATE:
      return "yellow";
    case SupplyLevels.LIMITED:
      return "lime";
    case SupplyLevels.SCARCE:
      return "green";
    default:
      return "black";
  }
}

export function getSupplyColor(type, supply) {
  switch (type) {
    case TradeGoodTypes.EXPORT:
      return getSupplyColorAscending(supply);
    case TradeGoodTypes.IMPORT:
      return getSupplyColorDescending(supply);
    default:
      return "black";
  }
}
