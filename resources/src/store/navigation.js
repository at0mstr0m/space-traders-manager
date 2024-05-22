import { defineStore } from "pinia";
import { ref } from "vue";
import { useRepository } from "@/repos/repoGenerator.js";
import waypointTraitSymbols from "@enums/waypointTraitSymbols";
import _uniqBy from "lodash/uniqBy";

const useNavigationStore = defineStore("navigation", () => {
  const repo = useRepository("waypoints");

  const currentSystem = ref(null);
  const currentShip = ref(null);
  const tradeOpportunities = ref({});
  const ships = ref({});
  const currentWaypoints = ref([]);

  async function fetchTradeOpportunities(waypoint) {
    currentWaypoints.value = _uniqBy([...currentWaypoints.value, waypoint], "id");
    const isMarketplace = waypoint.traits.some(
      (trait) => trait.symbol === waypointTraitSymbols.MARKETPLACE
    );
    if (!isMarketplace) return;
    const response = await repo.market(waypoint.id);
    tradeOpportunities.value[waypoint.id] = response.data.data;
  }

  async function fetchShips(waypoint) {
    currentWaypoints.value = _uniqBy([...currentWaypoints.value, waypoint], "id");
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

  return {
    currentSystem,
    currentShip,
    tradeOpportunities,
    ships,
    fetchTradeOpportunities,
    fetchShips,
    load,
    refresh,
    reset,
  };
});

export default useNavigationStore;
