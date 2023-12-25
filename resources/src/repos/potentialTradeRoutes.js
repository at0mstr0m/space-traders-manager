import Repository from "@/repos/";

class PotentialTradeRouteRepository extends Repository {
  refetch() {
    return this._get(this.resource + '/refetch');
  }
}

export default new PotentialTradeRouteRepository("potential-trade-routes");
