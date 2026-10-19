<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create departments')->only(['create', 'store']);
        $this->middleware('permission:edit departments')->only(['edit', 'update']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::all();
        return view('department.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('department.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentRequest $request)
    {
        $request->merge(['daily_patient_limit' => 200, 'category' => 'OPD']);
        $department = Department::create($request->all());
        return to_route('department.index')->with('message', 'Department created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        return view('department.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $department->update($request->all());
        return redirect()->route('department.index')
            ->with('message', 'Department updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        abort_unless(auth()->user()->hasRole('Super-Admin'), 403, 'Only a Super-Admin can delete records.');

        $department->delete();

        return redirect()->route('department.index')
            ->with('message', 'Department deleted successfully!');
    }
}
