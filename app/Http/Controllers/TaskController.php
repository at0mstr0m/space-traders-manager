<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Task;
use App\Actions\TriggerTasks;
use Illuminate\Http\Response;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return TaskResource::collection(Task::paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request): TaskResource
    {
        return $this->show(Task::create($request->validated()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): TaskResource
    {
        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Task $task): TaskResource
    {
        $task->fillAndSave($request->validated());

        return $this->show($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): Response
    {
        $task->delete();

        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function triggerAll(): Response
    {
        Cache::tags(['graphs'])->flush();
        TriggerTasks::run();

        return response()->noContent(Response::HTTP_OK);
    }
}
