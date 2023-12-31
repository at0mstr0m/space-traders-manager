import { ModelRepository } from "@/repos/";

class ContractsRepository extends ModelRepository {
  refetch() {
    return this._get(this.resource + "/refetch");
  }
}

export default new ContractsRepository("contracts");
