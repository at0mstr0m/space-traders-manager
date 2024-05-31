import { ModelRepository } from "@/repos/";

class ShipRepository extends ModelRepository {
  refetch() {
    return this._get(this.resource + "/refetch");
  }

  buy(shipType, waypointSymbol) {
    return this._post(this.resource + "/buy", {
      shipType: shipType,
      waypointSymbol: waypointSymbol,
    });
  }

  updateFlightMode(shipId, flightMode) {
    return this._patch(this.resource + "/" + shipId + "/update-flight-mode", {
      flightMode: flightMode,
    });
  }

  updateTask(shipId, taskId) {
    return this._patch(this.resource + "/" + shipId + "/update-task", {
      taskId: taskId,
    });
  }
  
  purchaseTradeGood(shipId, symbol, quantity) {
    return this._post(this.resource + "/" + shipId + "/purchase", {
      symbol: symbol,
      quantity: quantity,
    });
  }

  sellTradeGood(shipId, symbol, quantity) {
    return this._post(this.resource + "/" + shipId + "/sell", {
      symbol: symbol,
      quantity: quantity,
    });
  }

  refuel(shipId) {
    return this._post(this.resource + "/" + shipId + "/refuel");
  }

  navigate(shipId, waypointId) {
    return this._post(this.resource + "/" + shipId + "/navigate/" + waypointId);
  }
}

export default new ShipRepository("ships");
