const systemTypes = Object.freeze({
  NEUTRON_STAR: "NEUTRON_STAR",
  RED_STAR: "RED_STAR",
  ORANGE_STAR: "ORANGE_STAR",
  BLUE_STAR: "BLUE_STAR",
  YOUNG_STAR: "YOUNG_STAR",
  WHITE_DWARF: "WHITE_DWARF",
  BLACK_HOLE: "BLACK_HOLE",
  HYPERGIANT: "HYPERGIANT",
  NEBULA: "NEBULA",
  UNSTABLE: "UNSTABLE",
});

function getSystemColor(type) {
  switch (type) {
    case systemTypes.NEUTRON_STAR:
      return "#ffffff";
    case systemTypes.RED_STAR:
      return "#ff0000";
    case systemTypes.ORANGE_STAR:
      return "#ffa500";
    case systemTypes.BLUE_STAR:
      return "#3250a8";
    case systemTypes.YOUNG_STAR:
      return "#ded581";
    case systemTypes.WHITE_DWARF:
      return "#a1a09d";
    case systemTypes.BLACK_HOLE:
      return "#3e2040";
    case systemTypes.HYPERGIANT:
      return "#c810b2";
    case systemTypes.NEBULA:
      return "#e2e8df";
    case systemTypes.UNSTABLE:
      return "#418275";
    default:
      return "#00FF00";
  }
}

export default systemTypes;

export { getSystemColor };
