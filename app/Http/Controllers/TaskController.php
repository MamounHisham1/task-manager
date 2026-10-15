<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return TaskResource::collection(Task::query()->latest('id')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): TaskResource
    {
        $task = Task::create($request->validated());
        $task->refresh();

        return new TaskResource($task);
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
    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $task->update($request->validated());

        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): \Illuminate\Http\JsonResponse
    {
        $task->delete();

        return response()->json(null, 204);
    }
}
