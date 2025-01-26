<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobPostRequest;
use App\Http\Requests\UpdateJobPostRequest;
use App\Http\Resources\JobPostResource;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobPostController extends Controller
{
    public function index(Request $request)
    {
        $job_posts = JobPost::query()
            ->with('user')
            ->when(isset($request->user_id), function ($query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->when(isset($request->search), function ($query) use ($request) {
                $query->whereAny(['title', 'description', 'company', 'location'], 'like', "%{$request->search}%");
            })
            ->paginate(10);

        return JobPostResource::collection($job_posts);
    }

    public function store(StoreJobPostRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        $job_post = JobPost::create($data);

        return new JobPostResource($job_post);
    }

    public function show(JobPost $jobPost)
    {
        $jobPost->load('user');

        return new JobPostResource($jobPost);
    }

    public function update(UpdateJobPostRequest $request, JobPost $jobPost)
    {
        $jobPost->update($request->validated());

        return new JobPostResource($jobPost);
    }

    public function destroy(JobPost $jobPost)
    {
        if ($jobPost->user_id !== Auth::id()) {
            abort(403, "Unauthorized action.");
        }

        $jobPost->delete();

        return response()->noContent(200);
    }

    public function apply(JobPost $jobPost)
    {
        if ($jobPost->users()->where('user_id', Auth::id())->exists()) {
            abort(403, "You already applied this job post.");
        }

        $jobPost->users()->attach(Auth::id());

        return response()->noContent(200);
    }
}
