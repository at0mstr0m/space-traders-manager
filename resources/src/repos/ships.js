import Repository from "@/repos/";

class ShipsRepository extends Repository {
  refetch() {
    return this._get(this.resource + '/refetch');
  }
}

export default new ShipsRepository("ships");
