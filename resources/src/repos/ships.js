import { ModelRepository } from "@/repos/";

class ShipsRepository extends ModelRepository {
  refetch() {
    return this._get(this.resource + "/refetch");
  }

  purchase(shipType, waypointSymbol) {
    return this._post(this.resource + "/purchase", {
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
}

export default new ShipsRepository("ships");
