import api from "@/services/API.js";

export default class Repository {
  constructor(resource) {
    this.resource = resource;
    this._get = api.get;
  }

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
