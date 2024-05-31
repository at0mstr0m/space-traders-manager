import { ModelRepository } from "@/repos/";

class ContractRepository extends ModelRepository {
  refetch() {
    return this._get(this.resource + "/refetch");
  }
  accept(id) {
    return this._post(this.resource + "/" + id + "/accept");
  }
}

export default new ContractRepository("contracts");
