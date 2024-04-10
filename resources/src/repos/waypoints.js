import { ModelRepository } from "@/repos/";

class WaypointsRepository extends ModelRepository {
  withoutSatellite() {
    return this._get(this.resource + "/without-satellite");
  }
}

export default new WaypointsRepository("waypoints");
