import contractRepository from "@/repos/contracts.js";
import liveDataRepository from "@/repos/liveData.js";
import potentialTradeRouteRepository from "@/repos/potentialTradeRoutes.js";
import shipRepository from "@/repos/ships.js";
import systemRepository from "@/repos/systems.js";
import taskRepository from "@/repos/tasks.js";
import tradeOpportunityRepository from "@/repos/tradeOpportunities.js";
import transactionRepository from "@/repos/transactions.js";
import waypointRepository from "@/repos/waypoints.js";

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
    case "systems":
      return systemRepository;
    case "tasks":
      return taskRepository;
    case "trade-opportunities":
      return tradeOpportunityRepository;
    case "transactions":
      return transactionRepository;
    case "waypoints":
      return waypointRepository;
    default:
      throw new Error(`No repository found for resource: ${resource}`);
  }
}
