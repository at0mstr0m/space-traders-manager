import api from "@/services/API.js";
import _isPlainObject from "lodash/isPlainObject";

export class Repository {
  constructor(resource) {
    this.resource = resource;
    this._get = api.get;
    this._post = api.post;
    this._patch = api.patch;
    this._put = api.put;
    this._delete = api.delete;
  }
}

export class ModelRepository extends Repository {
  index(page = 1, perPage = 10, params = {}) {
    if (_isPlainObject(page)) {
      return this.index(
        page.page,
        page.perPage,
        page.params
      );
    }
    return this._get(this.resource, {
      params: {
        page: page,
        perPage: perPage,
        ...params,
      },
    });
  }

  show(id) {
    return this._get(this.resource + "/" + id);
  }
}
