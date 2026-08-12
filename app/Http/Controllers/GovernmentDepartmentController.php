<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGovernmentDepartmentRequest;
use App\Http\Requests\UpdateGovernmentDepartmentRequest;
use App\Models\Department;
use App\Models\GovernmentDepartment;

class GovernmentDepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = GovernmentDepartment::all();
        return view('government-departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('government-departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGovernmentDepartmentRequest $request)
    {
        $department = GovernmentDepartment::create($request->all());
        return to_route('governmentDepartment.index')->with('message', 'Government department created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(GovernmentDepartment $governmentDepartment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GovernmentDepartment $governmentDepartment)
    {
        $department = $governmentDepartment;
        return view('government-departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGovernmentDepartmentRequest $request, GovernmentDepartment $governmentDepartment)
    {
        $governmentDepartment->update($request->all());
        return redirect()->route('governmentDepartment.index')
            ->with('message', 'Government department updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GovernmentDepartment $governmentDepartment)
    {
        $governmentDepartment->delete();

        return redirect()->route('governmentDepartment.index')
            ->with('message', 'Government department deleted successfully!');
    }
}
