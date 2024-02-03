import { ModelRepository } from "@/repos/";

class TradeOpportunityRepository extends ModelRepository {
  refetch() {
    return this._get(this.resource + "/refetch");
  }
}

export default new TradeOpportunityRepository("trade-opportunities");
