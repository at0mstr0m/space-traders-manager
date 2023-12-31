import { ModelRepository } from "@/repos/";

class ShipsRepository extends ModelRepository {
  refetch() {
    return this._get(this.resource + "/refetch");
  }
}

export default new ShipsRepository("ships");
