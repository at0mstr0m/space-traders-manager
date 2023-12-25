import isNil from "lodash/isNil";

export default function useStringify() {
  function decimal(value) {
    if (isNil(value)) {
      return "";
    }
    return Math.floor(value * 100) / 100;
  }

  return {
    decimal
  };
}
