<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function today()
    {
        $user = \Auth::user();
        $issued_invoices = null;
        if ($user->hasRole('Front Desk/Receptionist')) {
            $issued_invoices = QueryBuilder::for(Invoice::class)
                ->allowedFilters(['patient_id', 'fee_type_id', 'government_department_id', 'issued_date', 'ipd_opd', 'government_card_no', AllowedFilter::exact('government_non_gov'), AllowedFilter::exact('id'), AllowedFilter::exact('department_id')])
                ->where('user_id', $user->id)->whereDate('created_at', Carbon::today())
//                ->where('user_id', $user->id)->where('ipd_opd', 1)->whereDate('issued_date', Carbon::today())
//                ->orderByDesc('created_at') // Corrected 'DSEC' to 'DESC'
                ->paginate(1000);

        } elseif ($user->hasRole(['Administrator', 'Super-Admin'])) {
            $issued_invoices = QueryBuilder::for(Invoice::class)
                ->allowedFilters(['patient_id', 'fee_type_id', AllowedFilter::exact('department_id')])
                ->whereDate('created_at', Carbon::today())
                ->orderByDesc('created_at') // Corrected 'DSEC' to 'DESC'
                ->paginate(1000);
        }

        return view('invoices.today', compact('issued_invoices'));
    }

    public function issued(Request $request)
    {
        $user = \Auth::user();
        $issued_invoices = null;

        $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $end_date = Carbon::parse($request->end_date)->format('Y-m-d');

        $date_start_at = $start_date.' 00:00:00';
        $date_end_at = $end_date.' 23:59:59';

        $allowedFilters = [
            AllowedFilter::exact('patient_id'),
            AllowedFilter::exact('user_id'),
            AllowedFilter::exact('patient_test.fee_type_id'),
            AllowedFilter::exact('government_non_government'),
            AllowedFilter::exact('patient.government_card_no'),
            AllowedFilter::exact('patient.sex'),
        ];

        if ($user->hasRole('Front Desk/Receptionist')) {
            $issued_invoices = QueryBuilder::for(Invoice::class)->with('patient_test', 'admission', 'patient', 'user')
                ->allowedFilters($allowedFilters)
                ->where('user_id', $user->id)
                ->whereBetween('created_at', [$date_start_at, $date_end_at])
                ->orderBy('created_at')
                ->paginate(100000)->withQueryString();
        } elseif ($user->hasRole(['Administrator', 'Super-Admin'])) {
            $issued_invoices = QueryBuilder::for(Invoice::class)->with('patient_test', 'admission', 'patient', 'user')
                ->allowedFilters($allowedFilters)
                ->whereBetween('created_at', [$date_start_at, $date_end_at])
                ->orderBy('created_at')
                ->paginate(100000)->withQueryString();
        }

        return view('invoices.issued', compact('issued_invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
