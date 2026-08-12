<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLabTestRequest;
use App\Http\Requests\UpdateLabTestRequest;
use App\Models\LabTest;

class LabTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $labTests = LabTest::all();
        return view('labTest.index', compact('labTests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('labTest.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLabTestRequest $request)
    {
        $labTest = LabTest::create($request->all());
        return to_route('labTest.index')->with('message', 'Lab test created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(LabTest $labTest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LabTest $labTest)
    {
        return view('labTest.edit', compact('labTest'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLabTestRequest $request, LabTest $labTest)
    {
        $labTest->update($request->all());
        return redirect()->route('labTest.index')->with('message', 'Lab test updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LabTest $labTest)
    {
        $labTest->delete();
        return redirect()->route('labTest.index')->with('message', 'Lab test deleted successfully!');
    }
}
