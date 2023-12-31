import { ModelRepository } from "@/repos/";

class PotentialTradeRouteRepository extends ModelRepository {
  refetch() {
    return this._get(this.resource + "/refetch");
  }
}

export default new PotentialTradeRouteRepository("potential-trade-routes");
