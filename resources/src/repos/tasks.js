import { ModelRepository } from "@/repos/";

class TasksRepository extends ModelRepository {
  store(newTaskData) {
    return this._post(this.resource, newTaskData);
  }

  update(taskId, updatedTaskData) {
    return this._put(this.resource + '/' + taskId, updatedTaskData);
  }

  delete(taskId) {
    return this._delete(this.resource + '/' + taskId);
  }
}

export default new TasksRepository("tasks");
