import { ModelRepository } from "@/repos/";

class WaypointRepository extends ModelRepository {
  withoutSatellite() {
    return this._get(this.resource + "/without-satellite");
  }
  tradeOpportunities(waypointId) {
    return this._get(this.resource + "/" + waypointId + "/trade-opportunities");
  }
  marketGoods(waypointId) {
    return this._get(this.resource + "/" + waypointId + "/market-goods");
  }
  ships(waypointId) {
    return this._get(this.resource + "/" + waypointId + "/ships");
  }
}

export default new WaypointRepository("waypoints");
