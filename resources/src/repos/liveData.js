import { Repository } from "@/repos/";

class LiveDataRepository extends Repository {
  purchasableShipsInSystem(page = 1, perPage = 10, params = {}) {
    return this._get(this.resource + "/purchasable-ships-in-system", {
      params: {
        page: page,
        perPage: perPage,
        ...params,
      },
    });
  }
}

export default new LiveDataRepository("live-data");
