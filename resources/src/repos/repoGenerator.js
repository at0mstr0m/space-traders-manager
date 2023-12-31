import liveDataRepository from "@/repos/liveData.js";
import potentialTradeRouteRepository from "@/repos/potentialTradeRoutes.js";
import shipRepository from "@/repos/ships.js";
import contractRepository from "@/repos/contracts.js";

export function useRepository(resource) {
  switch (resource) {
    case "contracts":
      return contractRepository;
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
