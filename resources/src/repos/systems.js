import { ModelRepository } from "@/repos/";
import _isPlainObject from "lodash/isPlainObject";

class SystemRepository extends ModelRepository {
  waypoints(systemId, page = 1, perPage = 10, params = {}) {
    if (_isPlainObject(systemId)) {
      return this.waypoints(
        systemId.systemId,
        systemId.page,
        systemId.perPage,
        systemId.params
      );
    }
    return this._get(this.resource + "/" + systemId + "/waypoints", {
      params: {
        page: page,
        perPage: perPage,
        ...params,
      },
    });
  }
}

export default new SystemRepository("systems");
