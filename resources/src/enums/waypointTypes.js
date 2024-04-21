const waypointTypes = Object.freeze({
  PLANET: "PLANET",
  GAS_GIANT: "GAS_GIANT",
  MOON: "MOON",
  ORBITAL_STATION: "ORBITAL_STATION",
  JUMP_GATE: "JUMP_GATE",
  ASTEROID_FIELD: "ASTEROID_FIELD",
  ASTEROID: "ASTEROID",
  ENGINEERED_ASTEROID: "ENGINEERED_ASTEROID",
  ASTEROID_BASE: "ASTEROID_BASE",
  NEBULA: "NEBULA",
  DEBRIS_FIELD: "DEBRIS_FIELD",
  GRAVITY_WELL: "GRAVITY_WELL",
  ARTIFICIAL_GRAVITY_WELL: "ARTIFICIAL_GRAVITY_WELL",
  FUEL_STATION: "FUEL_STATION",
});

function getWaypointColor(type) {
  switch (type) {
    case waypointTypes.PLANET:
      return "#3250a8";
    case waypointTypes.GAS_GIANT:
      return "#067b80";
    case waypointTypes.MOON:
      return "#1f2b33";
    case waypointTypes.ORBITAL_STATION:
      return "#703396";
    case waypointTypes.JUMP_GATE:
      return "#c810b2";
    case waypointTypes.ASTEROID_FIELD:
      return "#0f2e11";
      case waypointTypes.ASTEROID:
      return "#cfa557";
    case waypointTypes.ENGINEERED_ASTEROID:
      return "#ffa500";
    case waypointTypes.ASTEROID_BASE:
      return "#ff0000";
    case waypointTypes.NEBULA:
      return "#e2e8df";
    case waypointTypes.DEBRIS_FIELD:
      return "#ed9ad4";
    case waypointTypes.GRAVITY_WELL:
      return "#f87979";
    case waypointTypes.ARTIFICIAL_GRAVITY_WELL:
      return "#f87979";
    case waypointTypes.FUEL_STATION:
      return "#00FF00";
  }
}

export default waypointTypes;

export { getWaypointColor };
