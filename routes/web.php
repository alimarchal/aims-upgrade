<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return to_route('login');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:view dashboard');

    // Roles & Permissions Management
    Route::resource('roles', \App\Http\Controllers\RoleController::class)->middleware('permission:view roles');
    Route::resource('permissions', \App\Http\Controllers\PermissionController::class)->middleware('permission:manage permissions');

    // Users Management
    Route::resource('users', UserController::class)->middleware('permission:view users');

    // Departments
    Route::resource('department', \App\Http\Controllers\DepartmentController::class)->middleware('permission:view departments');
    Route::resource('governmentDepartment', \App\Http\Controllers\GovernmentDepartmentController::class)->middleware('permission:view government departments');

    // Lab Tests
    Route::resource('labTest', \App\Http\Controllers\LabTestController::class);

    // Patient Management
    Route::middleware('permission:view patients')->group(function () {
        Route::get('patient/{patient}/proceed', [\App\Http\Controllers\PatientController::class, 'proceed'])->name('patient.proceed');
        Route::post('patient/{patient}/add-to-cart', [\App\Http\Controllers\PatientController::class, 'add_to_cart'])->name('patient.add-to-cart');
        Route::delete('patient/{patientTestCart}', [\App\Http\Controllers\PatientController::class, 'proceed_cart_destroy'])->name('patient_cart.destroy');
        Route::post('patient/{patient}/generateInvoice', [\App\Http\Controllers\PatientController::class, 'proceed_to_invoice'])->name('patient.proceed_to_invoice');
        Route::get('patient/{patient}/{invoice}/show', [\App\Http\Controllers\PatientController::class, 'patient_invoice'])->name('patient.patient_invoice');
        Route::get('patient/{patient}/{invoice}/show/thermal-print', [\App\Http\Controllers\PatientController::class, 'patient_invoice_thermal_print'])->name('patient.patient_invoice_thermal_print');

        // Emergency Treatment
        Route::get('patient/{patient}/emergency-treatment', [\App\Http\Controllers\PatientController::class, 'emergency_treatment'])->name('patient.emergency_treatment');
        Route::post('patient/{patient}/emergency-treatment', [\App\Http\Controllers\PatientController::class, 'emergency_treatment_store'])->name('patient.emergency_treatment_store');

        Route::get('patient/{patient}/history', [\App\Http\Controllers\PatientController::class, 'patient_history'])->name('patient.history');
        Route::post('patient/invoice', [\App\Http\Controllers\PatientController::class, 'patient_test_invoice_generate'])->name('patient.patient_test_invoice_generate');

        Route::resource('patient', \App\Http\Controllers\PatientController::class);
        Route::get('patient/ipd/create', [\App\Http\Controllers\PatientController::class, 'createIPD'])->name('patient.create-ipd');
        Route::get('patient/opd/create', [\App\Http\Controllers\PatientController::class, 'createOPD'])->name('patient.create-opd');
        Route::post('patient/opd', [\App\Http\Controllers\PatientController::class, 'storeOPD'])->name('patient.store-opd');
        Route::post('patient/ipd', [\App\Http\Controllers\PatientController::class, 'storeIPD'])->name('patient.store-ipd');

        Route::get('patient/{patient}/actions', [\App\Http\Controllers\PatientController::class, 'patient_actions'])->name('patient.actions');
        Route::get('patient/{patient}/issued-chits', [\App\Http\Controllers\ChitController::class, 'issued_chits'])->name('patient.issued-chits');
        Route::get('patient/{patient}/issued-invoices', [\App\Http\Controllers\ChitController::class, 'issued_invoices'])->name('patient.issued-invoices');
        Route::get('patient/{patient}/issue-new-chit', [\App\Http\Controllers\ChitController::class, 'issue_new_chit'])->name('patient.issue-new-chit');
        Route::post('patient/{patient}/issue-new-chit', [\App\Http\Controllers\ChitController::class, 'issue_new_chit_store'])->name('patient.issue-new-chitStore');
    });

    // Fee Types
    Route::resource('feeType', \App\Http\Controllers\FeeTypeController::class)->middleware('permission:view fee types');
    Route::get('patient/{patient}/chit/{chit}', [\App\Http\Controllers\ChitController::class, 'print'])->name('chit.print');

    // Chits & Invoices
    Route::middleware('permission:view chits')->group(function () {
        Route::get('chits/issued-today', [\App\Http\Controllers\ChitController::class, 'today'])->name('chits.issued-today');
        Route::get('chits/issued', [\App\Http\Controllers\ChitController::class, 'issued'])->name('chits.issued');
    });

    Route::middleware('permission:view invoices')->group(function () {
        Route::get('invoice/issued-today', [\App\Http\Controllers\InvoiceController::class, 'today'])->name('invoice.issued-today');
        Route::get('invoice/issued', [\App\Http\Controllers\InvoiceController::class, 'issued'])->name('invoice.issued');
    });

    // Reports
    Route::middleware('permission:view reports')->group(function () {
        Route::get('reports', [\App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');

        Route::get('reports/opd', [\App\Http\Controllers\ReportsController::class, 'opd'])->name('reports.opd')->middleware('permission:view opd reports');
        Route::get('reports/opd/user-wise', [\App\Http\Controllers\ReportsController::class, 'reportOpdUserWise'])->name('reports.opd.user-wise')->middleware('permission:view opd reports');
        Route::get('reports/opd/specialist-fees', [\App\Http\Controllers\ReportsController::class, 'reportOpdSpecialistFees'])->name('reports.opd.specialist-fees')->middleware('permission:view opd reports');
        Route::get('reports/ipd', [\App\Http\Controllers\ReportsController::class, 'ipd'])->name('reports.ipd');
        Route::get('reports/opd/reports-daily', [\App\Http\Controllers\ReportsController::class, 'reportDaily'])->name('reports.opd.reportDaily')->middleware('permission:view daily reports');
        Route::get('reports/ipd/reports-daily', [\App\Http\Controllers\ReportsController::class, 'reportDailyIPD'])->name('reports.opd.reportDailyIPD')->middleware('permission:view daily reports');
        Route::get('reports/ipd/monthly-income-statement', [\App\Http\Controllers\ReportsController::class, 'monthlyIncomeStatement'])->name('reports.ipd.monthly-income-statement')->middleware('permission:view monthly income reports');

        Route::get('reports/misc', [\App\Http\Controllers\ReportsController::class, 'reportMisc'])->name('reports.misc');
        Route::get('reports/misc/admission', [\App\Http\Controllers\ReportsController::class, 'admission'])->name('reports.misc.admission')->middleware('permission:view admission reports');
        Route::get('reports/emergency-treatments', [\App\Http\Controllers\ReportsController::class, 'emergency_treatments'])->name('reports.emergency_treatments')->middleware('permission:view emergency reports');

        Route::get('reports/misc/department-wise', [\App\Http\Controllers\ReportsController::class, 'department_wise'])->name('reports.misc.category-wise')->middleware('permission:view department reports');
        Route::get('reports/misc/department-wise-two', [\App\Http\Controllers\ReportsController::class, 'department_wise_two'])->name('reports.misc.category-wise-two')->middleware('permission:view department reports');

        Route::get('reports/ssp/claims', [\App\Http\Controllers\ReportsController::class, 'sspClaims'])->name('reports.ssp.claims')->middleware('permission:view ssp reports');
    });

});
