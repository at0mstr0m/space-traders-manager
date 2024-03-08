import api from "@/services/API.js";

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
    return this._get(this.resource, {
      params: {
        page: page,
        perPage: perPage,
        ...params,
      },
    });
  }
}
