function parseLocation(symbol) {
  return symbol.split("-");
}

export function parseSystemSymbol(symbol) {
  return parseLocation(symbol)[0] + "-" + parseLocation(symbol)[1];
}