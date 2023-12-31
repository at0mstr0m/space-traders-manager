import liveDataRepository from "@/repos/liveData.js";
import potentialTradeRouteRepository from "@/repos/potentialTradeRoutes.js";
import shipRepository from "@/repos/ships.js";

export function useRepository(resource) {
  switch (resource) {
    case "live-data":
      return liveDataRepository;
    case "potential-trade-routes":
      return potentialTradeRouteRepository;
    case "ships":
      return shipRepository;
    default:
      throw new Error(`No repository found for resource: ${resource}`);
  }
}
