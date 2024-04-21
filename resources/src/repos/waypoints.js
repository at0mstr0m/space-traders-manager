import { ModelRepository } from "@/repos/";

class WaypointRepository extends ModelRepository {
  withoutSatellite() {
    return this._get(this.resource + "/without-satellite");
  }
}

export default new WaypointRepository("waypoints");
