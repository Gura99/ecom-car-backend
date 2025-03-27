<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        return $this->successResponse($tasks);
    }

    // Store a new task
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|in:pending,completed',
            // 'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048', // Added image validation
        ]);

        // Handle the image upload if provided
        // if ($request->hasFile('image')) {
        //     $imagePath = $request->file('image')->store('tasks/images', 'public'); // Store image in 'public/tasks/images' folder
        // } else {
        //     $imagePath = null;
        // }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            // 'image' => $imagePath, // Store the image path
        ]);

        return $this->successResponse($task, statusCode: 201);
    }

    // Show a specific task
    public function show($id)
    {
        $task = Task::findOrFail($id);
        return $this->successResponse($task, statusCode: 200);
    }

    // Update a task
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|in:pending,completed',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048', // Added image validation
        ]);

        // Handle the image upload if provided
        if ($request->hasFile('image')) {
            // If the task already has an image, delete the old one
            if ($task->image && Storage::disk('public')->exists($task->image)) {
                Storage::disk('public')->delete($task->image);
            }

            // Store the new image
            $imagePath = $request->file('image')->store('tasks/images', 'public');
        } else {
            $imagePath = $task->image; // Keep the existing image if no new one is uploaded
        }

        // Update task with new data
        $task->update([
            'title' => $request->title ?? $task->title,
            'description' => $request->description ?? $task->description,
            'status' => $request->status ?? $task->status,
            'image' => $imagePath, // Store the new image path or keep the old one
        ]);

        return $this->successResponse($task, statusCode: 200);
    }

    // Delete a task
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        // Delete the image if it exists
        if ($task->image && Storage::disk('public')->exists($task->image)) {
            Storage::disk('public')->delete($task->image);
        }

        $task->delete();

        return $this->successResponse("Task deleted successfully", statusCode: 200);
    }
}
