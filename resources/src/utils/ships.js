export default function useShipUtils() {
  const tableColumns = [
    {
      title: "Symbol",
      key: "symbol",
      width: "160px",
    },
    {
      title: "Role",
      key: "role",
    },
    {
      title: "Waypoint Symbol",
      key: "waypoint_symbol",
    },
    {
      title: "Task",
      key: "task.type",
    },
    {
      title: "Status",
      key: "status",
    },
    {
      title: "Flight Mode",
      key: "flight_mode",
    },
    {
      title: "Crew Current",
      key: "crew_current",
    },
    {
      title: "Crew Capacity",
      key: "crew_capacity",
    },
    {
      title: "Crew Required",
      key: "crew_required",
    },
    {
      title: "Crew Rotation",
      key: "crew_rotation",
    },
    {
      title: "Crew Morale",
      key: "crew_morale",
    },
    {
      title: "Crew Wages",
      key: "crew_wages",
    },
    {
      title: "Fuel Current",
      key: "fuel_current",
    },
    {
      title: "Fuel Capacity",
      key: "fuel_capacity",
    },
    {
      title: "Fuel Consumed",
      key: "fuel_consumed",
    },
    {
      title: "Cooldown",
      key: "cooldown",
    },
    {
      title: "Frame Condition",
      key: "frame_condition",
    },
    {
      title: "Frame Integrity",
      key: "frame_integrity",
    },
    {
      title: "Reactor Condition",
      key: "reactor_condition",
    },
    {
      title: "Frame Integrity",
      key: "frame_integrity",
    },
    {
      title: "Engine Condition",
      key: "engine_condition",
    },
    {
      title: "Engine Integrity",
      key: "engine_integrity",
    },
    {
      title: "Cargo Capacity",
      key: "cargo_capacity",
    },
    {
      title: "Cargo Units",
      key: "cargo_units",
    },
  ];

  const flightModes = [
    {
      title: "DRIFT",
      value: "DRIFT",
    },
    {
      title: "STEALTH",
      value: "STEALTH",
    },
    {
      title: "CRUISE",
      value: "CRUISE",
    },
    {
      title: "BURN",
      value: "BURN",
    },
  ];

  return {
    tableColumns,
    flightModes,
  };
}
