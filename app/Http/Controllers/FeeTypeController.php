<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeeTypeRequest;
use App\Http\Requests\UpdateFeeTypeRequest;
use App\Models\FeeCategory;
use App\Models\FeeType;

class FeeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = FeeCategory::with('feeTypes')->get();
        return view('fee-types.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('fee-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFeeTypeRequest $request)
    {
        if ($request->amount < 0){
            $request->merge(['status' => 'Return Fee']);
        }
        $feeTypes = FeeType::create($request->all());
        return to_route('feeType.index')->with('message', 'Fee type has been created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(FeeType $feeType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FeeType $feeType)
    {
        return view('fee-types.edit', compact('feeType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFeeTypeRequest $request, FeeType $feeType)
    {
        if ($request->amount < 0){
            $request->merge(['status' => 'Return Fee']);
        }
        $feeType->update($request->all());
        return to_route('feeType.index')->with('message', 'Fee type has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FeeType $feeType)
    {
        $feeType->delete();
        return redirect()->route('feeType.index')->with('message', 'Fee type deleted successfully!');
    }
}
