<?php

namespace App\Http\Controllers;

use App\Models\Projects as Project; // keep alias if your model is named Projects
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator; // added
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    // GET /client/projects
    public function index(Request $request)
    {

        $projects= Project::where('added_by', Auth::id())->get();
        return response()->json(['data'=>$projects]);
        $user = Auth::user();
        abort_unless($user && $user->role === 'client', 403, 'Forbidden');

        $perPage = (int) $request->get('per_page', 10);
        $search  = trim((string) $request->get('search', ''));
        $sort    = $request->get('sort', 'created_at');
        $dir     = $request->get('dir', 'desc');

        $query = Project::with('added_by.company')
            ->where('added_by', $user->id);

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $allowedSorts = ['name','created_at','updated_at'];
        if (!in_array($sort, $allowedSorts, true)) $sort = 'created_at';
        $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';

        return response()->json(
            $query->orderBy($sort, $dir)->paginate($perPage),
            200
        );
    }

    // GET /client/projects/{project}
    public function show($id)
{
    
    $project = Project::find($id);
    if (!$project) {
        return response()->json(['error' => 'Project not found'], 404);
    }
 
    
    $user = Auth::user();
    abort_unless($user && $user->role === 'client', 403, 'Forbidden');

    // compare against the FK column
    abort_unless((int) $project->added_by === (int) $user->id, 404, 'Project not found');
    

    $project->load('added_by.company');
    // dd($project);
    return response()->json($project, 200);
}

    // POST /client/projects
    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'client', 403, 'Forbidden');

        $validator = Validator::make($request->all(), [
            'name'               => ['required','string','max:255'],
            'site_contact_name'  => ['nullable','string','max:255'],
            'site_contact_phone' => ['nullable','string','max:50'],
            'site_instructions'  => ['nullable','string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        $project = Project::create([
            ...$data,
            'added_by' => $user->id, // lock to this client
        ]);

        return response()->json($project->load('added_by.company'), 201);
    }

    // PUT /client/projects/{project}
    public function update(Request $request, Project $project)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'client', 403, 'Forbidden');
        abort_unless($project->added_by === $user->id, 404, 'Project not found');

        $validator = Validator::make($request->all(), [
            'name'               => ['sometimes','required','string','max:255'],
            'site_contact_name'  => ['nullable','string','max:255'],
            'site_contact_phone' => ['nullable','string','max:50'],
            'site_instructions'  => ['nullable','string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        // do NOT change added_by
        $project->update($data);

        return response()->json($project->fresh()->load('added_by.company'), 200);
    }

    // DELETE /client/projects/{project}
    public function destroy($id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }
        
        $user = Auth::user();
        abort_unless($user && $user->role === 'client', 403, 'Forbidden');
        abort_unless($project->added_by === $user->id, 404, 'Project not found');

        $project->delete();
        return response()->json(['message' => 'Project deleted'], 200);
    }
}
