import shipRepository from "@/repos/ships.js";
import potentialTradeRouteRepository from "@/repos/potentialTradeRoutes.js";

export function useRepository(resource) {
  switch (resource) {
    case "ships":
      return shipRepository;
    case "potential-trade-routes":
      return potentialTradeRouteRepository;
    default:
      throw new Error(`No repository found for resource: ${resource}`);
  }
}
