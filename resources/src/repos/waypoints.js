import { ModelRepository } from "@/repos/";

class WaypointRepository extends ModelRepository {
  withoutSatellite() {
    return this._get(this.resource + "/without-satellite");
  }
  market(waypointId) {
    return this._get(this.resource + "/" + waypointId + "/market");
  }
  ships(waypointId) {
    return this._get(this.resource + "/" + waypointId + "/ships");
  }
}

export default new WaypointRepository("waypoints");
