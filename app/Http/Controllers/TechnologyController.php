<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTechnologyRequest;
use App\Http\Requests\UpdateTechnologyRequest;
use App\Models\Technology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TechnologyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Technology::class);

        $technologies = Technology::query()
            ->withCount('projects')
            ->latest()
            ->paginate(15);

        return view('admin.technologies.index', compact('technologies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Technology::class);

        return view('admin.technologies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTechnologyRequest $request): RedirectResponse
    {
        Technology::create($request->validated());

        return redirect()
            ->route('admin.technologies.index')
            ->with('success', 'Technology created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Technology $technology): View
    {
        Gate::authorize('update', $technology);

        return view('admin.technologies.edit', compact('technology'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTechnologyRequest $request, Technology $technology): RedirectResponse
    {
        $technology->update($request->validated());

        return redirect()
            ->route('admin.technologies.index')
            ->with('success', 'Technology updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Technology $technology): RedirectResponse
    {
        Gate::authorize('delete', $technology);

        $technology->delete();

        return redirect()
            ->route('admin.technologies.index')
            ->with('success', 'Technology deleted successfully.');
    }
}
