<?php

use App\Http\Controllers\ChitController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FeeTypeController;
use App\Http\Controllers\GovernmentDepartmentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LabTestController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RoleController;
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
    Route::resource('roles', RoleController::class)->middleware('permission:view roles');
    Route::resource('permissions', PermissionController::class)->middleware('permission:manage permissions');

    // Users Management
    Route::resource('users', UserController::class)->middleware('permission:view users');

    // Departments
    Route::resource('department', DepartmentController::class)->middleware('permission:view departments');
    Route::resource('governmentDepartment', GovernmentDepartmentController::class)->middleware('permission:view government departments');

    // Lab Tests
    Route::resource('labTest', LabTestController::class);

    // Patient Management
    Route::middleware('permission:view patients')->group(function () {
        Route::get('patient/{patient}/proceed', [PatientController::class, 'proceed'])->name('patient.proceed');
        Route::post('patient/{patient}/add-to-cart', [PatientController::class, 'add_to_cart'])->name('patient.add-to-cart');
        Route::delete('patient/{patientTestCart}', [PatientController::class, 'proceed_cart_destroy'])->name('patient_cart.destroy');
        Route::post('patient/{patient}/generateInvoice', [PatientController::class, 'proceed_to_invoice'])->name('patient.proceed_to_invoice');
        Route::get('patient/{patient}/{invoice}/show', [PatientController::class, 'patient_invoice'])->name('patient.patient_invoice');
        Route::get('patient/{patient}/{invoice}/show/thermal-print', [PatientController::class, 'patient_invoice_thermal_print'])->name('patient.patient_invoice_thermal_print');

        // Emergency Treatment
        Route::get('patient/{patient}/emergency-treatment', [PatientController::class, 'emergency_treatment'])->name('patient.emergency_treatment');
        Route::post('patient/{patient}/emergency-treatment', [PatientController::class, 'emergency_treatment_store'])->name('patient.emergency_treatment_store');

        Route::get('patient/{patient}/history', [PatientController::class, 'patient_history'])->name('patient.history');
        Route::post('patient/invoice', [PatientController::class, 'patient_test_invoice_generate'])->name('patient.patient_test_invoice_generate');

        Route::resource('patient', PatientController::class);
        Route::get('patient/ipd/create', [PatientController::class, 'createIPD'])->name('patient.create-ipd');
        Route::get('patient/opd/create', [PatientController::class, 'createOPD'])->name('patient.create-opd');
        Route::post('patient/opd', [PatientController::class, 'storeOPD'])->name('patient.store-opd');
        Route::post('patient/ipd', [PatientController::class, 'storeIPD'])->name('patient.store-ipd');

        Route::get('patient/{patient}/actions', [PatientController::class, 'patient_actions'])->name('patient.actions');
        Route::get('patient/{patient}/issued-chits', [ChitController::class, 'issued_chits'])->name('patient.issued-chits');
        Route::get('patient/{patient}/issued-invoices', [ChitController::class, 'issued_invoices'])->name('patient.issued-invoices');
        Route::get('patient/{patient}/issue-new-chit', [ChitController::class, 'issue_new_chit'])->name('patient.issue-new-chit');
        Route::post('patient/{patient}/issue-new-chit', [ChitController::class, 'issue_new_chit_store'])->name('patient.issue-new-chitStore');
    });

    // Fee Types
    Route::resource('feeType', FeeTypeController::class)->middleware('permission:view fee types');
    Route::get('patient/{patient}/chit/{chit}', [ChitController::class, 'print'])->name('chit.print');

    // Chits & Invoices
    Route::middleware('permission:view chits')->group(function () {
        Route::get('chits/issued-today', [ChitController::class, 'today'])->name('chits.issued-today');
        Route::get('chits/issued', [ChitController::class, 'issued'])->name('chits.issued');
    });

    Route::middleware('permission:view invoices')->group(function () {
        Route::get('invoice/issued-today', [InvoiceController::class, 'today'])->name('invoice.issued-today');
        Route::get('invoice/issued', [InvoiceController::class, 'issued'])->name('invoice.issued');
    });

    // Reports
    Route::middleware('permission:view reports')->group(function () {
        Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');

        Route::get('reports/opd', [ReportsController::class, 'opd'])->name('reports.opd')->middleware('permission:view opd reports');
        Route::get('reports/opd/user-wise', [ReportsController::class, 'reportOpdUserWise'])->name('reports.opd.user-wise')->middleware('permission:view opd reports');
        Route::get('reports/opd/specialist-fees', [ReportsController::class, 'reportOpdSpecialistFees'])->name('reports.opd.specialist-fees')->middleware('permission:view opd reports');
        Route::get('reports/ipd', [ReportsController::class, 'ipd'])->name('reports.ipd');
        Route::get('reports/opd/reports-daily', [ReportsController::class, 'reportDaily'])->name('reports.opd.reportDaily')->middleware('permission:view daily reports');
        Route::get('reports/ipd/reports-daily', [ReportsController::class, 'reportDailyIPD'])->name('reports.opd.reportDailyIPD')->middleware('permission:view daily reports');
        Route::get('reports/ipd/monthly-income-statement', [ReportsController::class, 'monthlyIncomeStatement'])->name('reports.ipd.monthly-income-statement')->middleware('permission:view monthly income reports');

        Route::get('reports/misc', [ReportsController::class, 'reportMisc'])->name('reports.misc');
        Route::get('reports/misc/admission', [ReportsController::class, 'admission'])->name('reports.misc.admission')->middleware('permission:view admission reports');
        Route::get('reports/emergency-treatments', [ReportsController::class, 'emergency_treatments'])->name('reports.emergency_treatments')->middleware('permission:view emergency reports');

        Route::get('reports/misc/department-wise', [ReportsController::class, 'department_wise'])->name('reports.misc.category-wise')->middleware('permission:view department reports');
        Route::get('reports/misc/department-wise-two', [ReportsController::class, 'department_wise_two'])->name('reports.misc.category-wise-two')->middleware('permission:view department reports');
        Route::get('reports/misc/department-wise-audit', [ReportsController::class, 'department_wise_audit'])->name('reports.misc.department-wise-audit')->middleware('permission:view department wise audit reports');

        Route::get('reports/ssp/claims', [ReportsController::class, 'sspClaims'])->name('reports.ssp.claims')->middleware('permission:view ssp reports');
    });

});
