<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\StoreTaskAttachmentRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Task::where('user_id', auth()->id())
            ->orderBy('due_date')
            ->filter(request(['status', 'priority']))
            ->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest  $request)
    {
        $task = $request->user()->tasks()->create($request->validated());
        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return $task;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest  $request, Task $task)
    {
        $this->authorize('update', $task);
        $task->update($request->validated());
        return $task;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        return response()->noContent();
    }

    public function complete(Task $task)
    {
        $this->authorize('update', $task);
        $task->update([
            'is_completed' => true,
            'completed_at' => now()
        ]);
        return $task;
    }

    public function attachFile(StoreTaskAttachmentRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $file = $request->file('attachment');
        $fileName = time().'_'.Str::slug($file->getClientOriginalName());
        $path = $file->storeAs('task_attachments', $fileName);

        $task->update([
            'attachment_path' => $path,
            'attachment_name' => $file->getClientOriginalName()
        ]);

        return response()->json([
            'message' => 'File attached successfully',
            'attachment' => $task->only(['attachment_path', 'attachment_name'])
        ]);
    }

}
