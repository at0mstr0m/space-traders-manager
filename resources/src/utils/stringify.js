import isNil from "lodash/isNil";

export default function useStringify() {
  function decimal(value) {
    if (isNil(value)) {
      return "";
    }
    return Math.floor(value * 100) / 100;
  }

  // https://stackoverflow.com/a/2901298
  function integer(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  return {
    decimal,
    integer,
  };
}
