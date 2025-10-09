<?php

namespace App\Http\Controllers;

use App\Models\Projects as Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    // List all projects with pagination
    public function index(Request $request)
    {
        // Default pagination parameters
        $perPage = $request->get('per_page', 10);  // Default 10 items per page
        $page = $request->get('page', 1);  // Default page number is 1

        // Fetch projects with pagination
        $projects = Project::with('added_by.company')->paginate($perPage);

        return response()->json($projects, 200);
    }

    // Show a specific project by ID
    public function show($id)
    {
        $project = Project::with('added_by.company')->find($id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        return response()->json($project, 200);
    }

    // Create a new project
    public function store(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'site_contact_name' => 'nullable|string|max:255',
            'site_contact_phone' => 'nullable|string|max:50',
            'site_instructions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create the project
        $project = Project::create([
            'name' => $request->name,
            'added_by' => Auth::id(),  // Assuming the authenticated user is adding the project
            'site_contact_name' => $request->site_contact_name,
            'site_contact_phone' => $request->site_contact_phone,
            'site_instructions' => $request->site_instructions,
        ]);

        return response()->json($project, 201);
    }

    // Update a project by ID
    public function update(Request $request, $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'site_contact_name' => 'nullable|string|max:255',
            'site_contact_phone' => 'nullable|string|max:50',
            'site_instructions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Update the project
        $project->update([
            'name' => $request->name ?? $project->name,
            'added_by' => Auth::id() ?? $project->added_by,
            'site_contact_name' => $request->site_contact_name ?? $project->site_contact_name,
            'site_contact_phone' => $request->site_contact_phone ?? $project->site_contact_phone,
            'site_instructions' => $request->site_instructions ?? $project->site_instructions,
        ]);

        return response()->json($project, 200);
    }

    // Delete a project by ID
    public function destroy($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully'], 200);
    }
}
