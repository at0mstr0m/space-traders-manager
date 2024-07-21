import { defineStore } from "pinia";
import { ref, watch } from "vue";
import { useRepository } from "@/repos/repoGenerator.js";
import waypointTraitSymbols from "@enums/waypointTraitSymbols";
import _uniqBy from "lodash/uniqBy";

const useNavigationStore = defineStore("navigation", () => {
  const repo = useRepository("waypoints");

  const allSystems = ref(
    localStorage.getItem("allSystems")
      ? JSON.parse(localStorage.getItem("allSystems"))
      : []
  );
  const currentTab = ref("ships");
  const currentSystem = ref(null);
  const currentShip = ref(null);
  const tradeOpportunities = ref({});
  const ships = ref({});
  const currentWaypoints = ref([]);
  const currentSystemWaypoints = ref([]);

  async function fetchTradeOpportunities(waypoint) {
    currentWaypoints.value = _uniqBy(
      [...currentWaypoints.value, waypoint],
      "id"
    );
    const isMarketplace = waypoint.traits.some(
      (trait) => trait.symbol === waypointTraitSymbols.MARKETPLACE
    );
    if (!isMarketplace) return;
    const response = await repo.market(waypoint.id);
    tradeOpportunities.value[waypoint.id] = response.data.data;
  }

  async function fetchShips(waypoint) {
    currentWaypoints.value = _uniqBy(
      [...currentWaypoints.value, waypoint],
      "id"
    );
    if (!waypoint.id) return;
    const response = await repo.ships(waypoint.id);
    ships.value[waypoint.id] = response.data.data;
  }

  function load(waypoint) {
    fetchTradeOpportunities(waypoint);
    fetchShips(waypoint);
  }

  function refresh() {
    const waypoints = currentWaypoints.value;
    reset();
    waypoints.forEach(load);
  }

  function reset() {
    tradeOpportunities.value = {};
    ships.value = {};
    currentShip.value = null;
    currentWaypoints.value = [];
  }

  function addToAllSystems(systems) {
    allSystems.value = _uniqBy([...allSystems.value, ...systems], "id");
    localStorage.setItem("allSystems", JSON.stringify(allSystems.value));
  }

  function getSystemFromWaypointSymbol(waypointSymbol) {
    const systemSymbol = waypointSymbol.substring(
      0,
      waypointSymbol.lastIndexOf("-")
    );
    return allSystems.value.find((system) => system.symbol === systemSymbol);
  }

  function pushToCurrentSystemWaypoints(...waypoints) {
    currentSystemWaypoints.value.push(...waypoints);
  }

  watch(currentSystem, () => {
    currentSystemWaypoints.value = [];
  });

  return {
    allSystems,
    currentTab,
    currentSystem,
    currentShip,
    tradeOpportunities,
    ships,
    currentSystemWaypoints,
    fetchTradeOpportunities,
    fetchShips,
    load,
    refresh,
    reset,
    addToAllSystems,
    getSystemFromWaypointSymbol,
    pushToCurrentSystemWaypoints,
  };
});

export default useNavigationStore;
