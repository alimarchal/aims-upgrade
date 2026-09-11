# Graph Report - aimsu  (2026-09-11)

## Corpus Check
- 413 files · ~293,444 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1750 nodes · 2913 edges · 272 communities (79 shown, 30 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 54 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `d576b7ea`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- jquery-3.6.0.min.js
- User
- Chit
- FortifyServiceProvider.php
- package.json
- PatientTestCart
- Patient
- AdmissionController
- Invoice
- LabTest
- PatientAttendantRelation
- Tehsil
- TotalFee
- Department
- migrate_aims_to_pgsql.py
- Team
- Controller
- AGENTS.md
- Illuminate\Database\Eloquent\Factories\HasFactory
- JetstreamServiceProvider.php
- FeeType
- Illuminate\Database\Eloquent\Factories\Factory
- Illuminate\Database\Migrations\Migration
- Illuminate\Database\Seeder
- Livewire\Livewire
- AddTeamMember.php
- Illuminate\Foundation\Http\FormRequest
- Laravel\Jetstream\Features
- AdmissionWardController
- District
- FeeCategoryController
- GovernmentDepartment
- PatientEmergencyTreatmentController
- PatientTestController
- Illuminate\Database\Eloquent\Relations\BelongsTo
- PatientTest
- Illuminate\Support\Facades\Schema
- FeeCategory
- CLAUDE.md
- PatientEmergencyTreatment
- SpecialistFeesReportTest.php
- require
- Laravel\Fortify\Features
- GEMINI.md
- DepartmentController
- composer.json
- require-dev
- DeleteUser.php
- PatientController.php
- team-member-manager.blade.php
- Illuminate\Database\Schema\Blueprint
- RemoveTeamMember.php
- web.php
- Laravel Boost Guidelines
- Livewire\Component
- config
- api-token-manager.blade.php
- EmergencyFeeTypeSeeder
- 2026_08_13_100606_add_dashboard_report_indexes.php
- 2026_08_13_101316_add_role_dashboard_three_indexes.php
- StoreChitRequest
- Livewire Development
- psr-4
- scripts
- logging.php
- herd
- FeeTypeController
- Livewire Development
- Pest Testing 3
- Pest Testing 3
- Tailwind CSS Development
- UpdatePatientRequest
- bootstrap/app.php
- logout-other-browser-sessions-form.blade.php
- delete-user-form.blade.php
- console.php
- Membership.php
- extra
- confirms-password.blade.php
- delete-team-form.blade.php
- issue_new_chit.blade.php
- tailwind.config.js
- sanctum.php
- deleteProfilePhoto
- patient/create.blade.php
- create-ipd.blade.php
- create-opd.blade.php
- patient/edit.blade.php
- Illuminate\Auth\Access\Response
- Tailwind CSS Development
- Do Things the Laravel Way
- InviteTeamMember.php
- Admission
- AdmissionWard
- Pest
- CreateNewUser.php
- CreateTeam.php
- UpdateDepartmentRequest
- UserFactory
- Livewire 3
- StoreHifRequest
- UpdateChitRequest
- Laravel 12
- GovernmentDepartmentSeeder.php
- PHP
- Tailwind CSS
- Searching Documentation (Critically Important)
- policy.md
- terms.md

## God Nodes (most connected - your core abstractions)
1. `User` - 233 edges
2. `Patient` - 54 edges
3. `Department` - 48 edges
4. `Chit` - 46 edges
5. `FeeType` - 39 edges
6. `Invoice` - 39 edges
7. `FeeCategory` - 36 edges
8. `Team` - 35 edges
9. `PatientTest` - 31 edges
10. `Laravel Boost Guidelines` - 31 edges

## Surprising Connections (you probably didn't know these)
- `CreateNewUser` --mixes_in--> `PasswordValidationRules`  [EXTRACTED]
  app/Actions/Fortify/CreateNewUser.php → app/Actions/Fortify/PasswordValidationRules.php
- `AdmissionController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/AdmissionController.php → app/Http/Controllers/Controller.php
- `AdmissionWardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/AdmissionWardController.php → app/Http/Controllers/Controller.php
- `ChitController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/ChitController.php → app/Http/Controllers/Controller.php
- `DashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (272 total, 30 thin omitted)

### Community 0 - "jquery-3.6.0.min.js"
Cohesion: 0.05
Nodes (56): a(), c(), d(), e(), f(), g(), h(), i() (+48 more)

### Community 1 - "User"
Cohesion: 0.10
Nodes (11): BelongsTo, User, ChitPolicy, PatientPolicy, TotalFeePolicy, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Fortify\TwoFactorAuthenticatable (+3 more)

### Community 2 - "Chit"
Cohesion: 0.13
Nodes (5): ChitController, ReportsController, Chit, Illuminate\Contracts\Http\Kernel, Illuminate\Http\Request

### Community 3 - "FortifyServiceProvider.php"
Cohesion: 0.12
Nodes (11): PasswordValidationRules, ResetUserPassword, UpdateUserPassword, FortifyServiceProvider, Illuminate\Cache\RateLimiting\Limit, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\Validator, Laravel\Fortify\Contracts\ResetsUserPasswords (+3 more)

### Community 4 - "package.json"
Cohesion: 0.07
Nodes (28): dependencies, watch, devDependencies, alpinejs, @alpinejs/focus, autoprefixer, axios, laravel-vite-plugin (+20 more)

### Community 5 - "PatientTestCart"
Cohesion: 0.09
Nodes (5): PatientTestCartController, StorePatientTestCartRequest, UpdatePatientTestCartRequest, PatientTestCart, PatientTestCartPolicy

### Community 6 - "Patient"
Cohesion: 0.11
Nodes (3): PatientController, Patient, Illuminate\Database\Eloquent\Relations\HasMany

### Community 7 - "AdmissionController"
Cohesion: 0.13
Nodes (3): AdmissionController, StoreAdmissionRequest, UpdateAdmissionRequest

### Community 8 - "Invoice"
Cohesion: 0.09
Nodes (6): InvoiceController, ProcessInvoicesController, UpdateInvoiceRequest, Invoice, InvoicePolicy, Illuminate\Database\Eloquent\Relations\HasOne

### Community 9 - "LabTest"
Cohesion: 0.17
Nodes (4): LabTestController, StoreLabTestRequest, UpdateLabTestRequest, LabTest

### Community 10 - "PatientAttendantRelation"
Cohesion: 0.11
Nodes (5): PatientAttendantRelationController, StorePatientAttendantRelationRequest, UpdatePatientAttendantRelationRequest, PatientAttendantRelation, PatientAttendantRelationPolicy

### Community 11 - "Tehsil"
Cohesion: 0.11
Nodes (5): TehsilController, StoreTehsilRequest, UpdateTehsilRequest, Tehsil, TehsilPolicy

### Community 12 - "TotalFee"
Cohesion: 0.15
Nodes (4): TotalFeeController, StoreTotalFeeRequest, UpdateTotalFeeRequest, TotalFee

### Community 13 - "Department"
Cohesion: 0.23
Nodes (3): Department, DepartmentPolicy, ChitFactory

### Community 14 - "migrate_aims_to_pgsql.py"
Cohesion: 0.17
Nodes (21): Any, Namespace, build_import_order(), copy_table(), DbConfig, destination_table_exists(), fetch_source_tables(), get_dest_columns_and_types() (+13 more)

### Community 15 - "Team"
Cohesion: 0.18
Nodes (7): Team, TeamPolicy, Illuminate\Auth\Access\HandlesAuthorization, Laravel\Jetstream\Events\TeamCreated, Laravel\Jetstream\Events\TeamDeleted, Laravel\Jetstream\Events\TeamUpdated, Laravel\Jetstream\Team

### Community 16 - "Controller"
Cohesion: 0.06
Nodes (17): UpdateUserProfileInformation, Controller, PermissionController, RoleController, UserController, RolesAndPermissionsSeeder, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Foundation\Auth\Access\AuthorizesRequests (+9 more)

### Community 17 - "AGENTS.md"
Cohesion: 0.05
Nodes (42): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Authentication & Authorization, Available Search Syntax, Comments, Configuration, Constructors (+34 more)

### Community 18 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.19
Nodes (4): Disease, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\SoftDeletes

### Community 19 - "JetstreamServiceProvider.php"
Cohesion: 0.17
Nodes (7): UpdateTeamName, AppServiceProvider, JetstreamServiceProvider, Illuminate\Foundation\AliasLoader, Illuminate\Support\Facades\Gate, Illuminate\Support\ServiceProvider, Laravel\Jetstream\Contracts\UpdatesTeamNames

### Community 20 - "FeeType"
Cohesion: 0.19
Nodes (3): FeeType, FeeTypePolicy, Illuminate\Support\Facades\Hash

### Community 21 - "Illuminate\Database\Eloquent\Factories\Factory"
Cohesion: 0.14
Nodes (7): DepartmentFactory, FeeCategoryFactory, FeeTypeFactory, GovernmentDepartmentFactory, PatientFactory, TeamFactory, Illuminate\Database\Eloquent\Factories\Factory

### Community 23 - "Illuminate\Database\Seeder"
Cohesion: 0.12
Nodes (8): AdmissionWardSeeder, DatabaseSeeder, DepartmentSeeder, DiseaseSeeder, LocationSeeder, PatientAttendantRelationSeeder, UserSeeder, Illuminate\Database\Seeder

### Community 24 - "Livewire\Livewire"
Cohesion: 0.10
Nodes (11): Illuminate\Support\Facades\Mail, Laravel\Jetstream\Http\Livewire\CreateTeamForm, Laravel\Jetstream\Http\Livewire\DeleteTeamForm, Laravel\Jetstream\Http\Livewire\LogoutOtherBrowserSessionsForm, Laravel\Jetstream\Http\Livewire\TeamMemberManager, Laravel\Jetstream\Http\Livewire\TwoFactorAuthenticationForm, Laravel\Jetstream\Http\Livewire\UpdatePasswordForm, Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm (+3 more)

### Community 25 - "AddTeamMember.php"
Cohesion: 0.29
Nodes (5): AddTeamMember, Laravel\Jetstream\Contracts\AddsTeamMembers, Laravel\Jetstream\Events\AddingTeamMember, Laravel\Jetstream\Events\TeamMemberAdded, Laravel\Jetstream\Rules\Role

### Community 26 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.11
Nodes (6): StoreGovernmentFeeRequest, StoreInvoiceRequest, StorePatientRequest, UpdateGovernmentFeeRequest, UpdateHifRequest, Illuminate\Foundation\Http\FormRequest

### Community 27 - "Laravel\Jetstream\Features"
Cohesion: 0.17
Nodes (5): Illuminate\Support\Str, Laravel\Jetstream\Features, Laravel\Jetstream\Http\Livewire\ApiTokenManager, Laravel\Jetstream\Http\Livewire\DeleteUserForm, Laravel\Jetstream\Http\Middleware\AuthenticateSession

### Community 28 - "AdmissionWardController"
Cohesion: 0.13
Nodes (3): AdmissionWardController, StoreAdmissionWardRequest, UpdateAdmissionWardRequest

### Community 29 - "District"
Cohesion: 0.11
Nodes (5): DistrictController, StoreDistrictRequest, UpdateDistrictRequest, District, DistrictPolicy

### Community 30 - "FeeCategoryController"
Cohesion: 0.13
Nodes (3): FeeCategoryController, StoreFeeCategoryRequest, UpdateFeeCategoryRequest

### Community 31 - "GovernmentDepartment"
Cohesion: 0.12
Nodes (5): GovernmentDepartmentController, StoreGovernmentDepartmentRequest, UpdateGovernmentDepartmentRequest, GovernmentDepartment, GovernmentDepartmentPolicy

### Community 32 - "PatientEmergencyTreatmentController"
Cohesion: 0.13
Nodes (3): PatientEmergencyTreatmentController, StorePatientEmergencyTreatmentRequest, UpdatePatientEmergencyTreatmentRequest

### Community 33 - "PatientTestController"
Cohesion: 0.13
Nodes (3): PatientTestController, StorePatientTestRequest, UpdatePatientTestRequest

### Community 34 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.13
Nodes (3): TeamInvitation, Illuminate\Database\Eloquent\Relations\BelongsTo, Laravel\Jetstream\TeamInvitation

### Community 37 - "FeeCategory"
Cohesion: 0.21
Nodes (3): FeeCategory, FeeCategoryPolicy, FeeSeeder

### Community 38 - "CLAUDE.md"
Cohesion: 0.05
Nodes (42): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Authentication & Authorization, Available Search Syntax, Comments, Configuration, Constructors (+34 more)

### Community 40 - "SpecialistFeesReportTest.php"
Cohesion: 0.21
Nodes (5): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, PHPUnit\Framework\Attributes\Test, SpecialistFeesReportTest, TestCase

### Community 41 - "require"
Cohesion: 0.17
Nodes (12): require, guzzlehttp/guzzle, laravel/framework, laravel/jetstream, laravel/sanctum, laravel/tinker, livewire/livewire, milon/barcode (+4 more)

### Community 42 - "Laravel\Fortify\Features"
Cohesion: 0.20
Nodes (6): Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\URL, Laravel\Fortify\Features

### Community 43 - "GEMINI.md"
Cohesion: 0.05
Nodes (42): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Authentication & Authorization, Available Search Syntax, Comments, Configuration, Constructors (+34 more)

### Community 45 - "composer.json"
Cohesion: 0.18
Nodes (10): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+2 more)

### Community 46 - "require-dev"
Cohesion: 0.18
Nodes (11): require-dev, fakerphp/faker, kitloong/laravel-migrations-generator, laravel/boost, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision (+3 more)

### Community 47 - "DeleteUser.php"
Cohesion: 0.31
Nodes (4): DeleteTeam, DeleteUser, Laravel\Jetstream\Contracts\DeletesTeams, Laravel\Jetstream\Contracts\DeletesUsers

### Community 48 - "PatientController.php"
Cohesion: 0.42
Nodes (5): Carbon\Carbon, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Log, Spatie\QueryBuilder\AllowedFilter, Spatie\QueryBuilder\QueryBuilder

### Community 49 - "team-member-manager.blade.php"
Cohesion: 0.20
Nodes (9): cancelTeamInvitation({{ $invitation->id }}), confirmTeamMemberRemoval(, leaveTeam, manageRole(, removeTeamMember, $set(, $toggle(, stopManagingRole (+1 more)

### Community 50 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.33
Nodes (6): createIndexIfNotExists(), down(), dropIndexIfExists(), indexExists(), up(), Illuminate\Database\Schema\Blueprint

### Community 51 - "RemoveTeamMember.php"
Cohesion: 0.31
Nodes (5): RemoveTeamMember, Illuminate\Auth\Access\AuthorizationException, Illuminate\Validation\ValidationException, Laravel\Jetstream\Contracts\RemovesTeamMembers, Laravel\Jetstream\Events\TeamMemberRemoved

### Community 52 - "web.php"
Cohesion: 0.22
Nodes (6): DashboardController, AppLayout, GuestLayout, Illuminate\Support\Facades\Route, Illuminate\View\Component, Illuminate\View\View

### Community 53 - "Laravel Boost Guidelines"
Cohesion: 0.08
Nodes (24): Application Structure & Architecture, Artisan, Comments, Conventions, Documentation Files, Enums, Foundational Context, Frontend Bundling (+16 more)

### Community 54 - "Livewire\Component"
Cohesion: 0.38
Nodes (3): GovernmentDetails, IpdOpd, Livewire\Component

### Community 55 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 56 - "api-token-manager.blade.php"
Cohesion: 0.29
Nodes (6): confirmApiTokenDeletion({{ $token->id }}), deleteApiToken, manageApiTokenPermissions({{ $token->id }}), $set(, $toggle(, updateApiToken

### Community 58 - "2026_08_13_100606_add_dashboard_report_indexes.php"
Cohesion: 0.60
Nodes (5): createIndexIfNotExists(), down(), dropIndexIfExists(), indexExists(), up()

### Community 59 - "2026_08_13_101316_add_role_dashboard_three_indexes.php"
Cohesion: 0.60
Nodes (5): createIndexIfNotExists(), down(), dropIndexIfExists(), indexExists(), up()

### Community 61 - "Livewire Development"
Cohesion: 0.11
Nodes (17): Alpine Integration, Basic Usage, Best Practices, Common Pitfalls, Component Structure, Creating Components, Documentation, Fundamental Concepts (+9 more)

### Community 62 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 63 - "scripts"
Cohesion: 0.40
Nodes (5): scripts, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd

### Community 64 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 65 - "herd"
Cohesion: 0.50
Nodes (4): SITE_PATH, php, herd, laravel-boost

### Community 66 - "FeeTypeController"
Cohesion: 0.14
Nodes (3): FeeTypeController, StoreFeeTypeRequest, UpdateFeeTypeRequest

### Community 67 - "Livewire Development"
Cohesion: 0.11
Nodes (17): Alpine Integration, Basic Usage, Best Practices, Common Pitfalls, Component Structure, Creating Components, Documentation, Fundamental Concepts (+9 more)

### Community 68 - "Pest Testing 3"
Cohesion: 0.12
Nodes (15): Architecture Testing, Assertions, Basic Test Structure, Basic Usage, Common Pitfalls, Creating Tests, Datasets, Documentation (+7 more)

### Community 69 - "Pest Testing 3"
Cohesion: 0.12
Nodes (15): Architecture Testing, Assertions, Basic Test Structure, Basic Usage, Common Pitfalls, Creating Tests, Datasets, Documentation (+7 more)

### Community 70 - "Tailwind CSS Development"
Cohesion: 0.15
Nodes (12): Basic Usage, Common Patterns, Common Pitfalls, Dark Mode, Documentation, Flexbox Layout, Grid Layout, Spacing (+4 more)

### Community 72 - "bootstrap/app.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware

### Community 73 - "logout-other-browser-sessions-form.blade.php"
Cohesion: 0.50
Nodes (3): confirmLogout, logoutOtherBrowserSessions, $toggle(

### Community 74 - "delete-user-form.blade.php"
Cohesion: 0.50
Nodes (3): confirmUserDeletion, deleteUser, $toggle(

### Community 75 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 77 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 252 - "Tailwind CSS Development"
Cohesion: 0.15
Nodes (12): Basic Usage, Common Patterns, Common Pitfalls, Dark Mode, Documentation, Flexbox Layout, Grid Layout, Spacing (+4 more)

### Community 253 - "Do Things the Laravel Way"
Cohesion: 0.18
Nodes (11): APIs & Eloquent Resources, Authentication & Authorization, Configuration, Controllers & Validation, Database, Do Things the Laravel Way, Model Creation, Queues (+3 more)

### Community 254 - "InviteTeamMember.php"
Cohesion: 0.31
Nodes (5): InviteTeamMember, Closure, Illuminate\Database\Query\Builder, Laravel\Jetstream\Contracts\InvitesTeamMembers, Laravel\Jetstream\Events\InvitingTeamMember

### Community 257 - "Pest"
Cohesion: 0.29
Nodes (7): Datasets, Mocking, Pest, Pest Assertions, Pest Tests, Running Tests, Testing

### Community 258 - "CreateNewUser.php"
Cohesion: 0.47
Nodes (3): CreateNewUser, Laravel\Fortify\Contracts\CreatesNewUsers, Laravel\Jetstream\Jetstream

### Community 259 - "CreateTeam.php"
Cohesion: 0.60
Nodes (3): CreateTeam, Laravel\Jetstream\Contracts\CreatesTeams, Laravel\Jetstream\Events\AddingTeam

### Community 262 - "Livewire 3"
Cohesion: 0.40
Nodes (5): Alpine, Key Changes From Livewire 2, Lifecycle Hooks, Livewire 3, New Directives

### Community 265 - "Laravel 12"
Cohesion: 0.50
Nodes (4): Database, Laravel 12, Laravel 12 Structure, Models

### Community 267 - "PHP"
Cohesion: 0.67
Nodes (3): Constructors, PHP, Type Declarations

### Community 268 - "Tailwind CSS"
Cohesion: 0.67
Nodes (3): Dark Mode, Spacing, Tailwind CSS

## Knowledge Gaps
- **319 isolated node(s):** `SITE_PATH`, `name`, `type`, `description`, `keywords` (+314 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 769 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **30 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `AdmissionWard`, `CreateNewUser.php`, `FortifyServiceProvider.php`, `CreateTeam.php`, `Chit`, `PatientTestCart`, `Invoice`, `PatientAttendantRelation`, `Tehsil`, `Department`, `Team`, `Controller`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `JetstreamServiceProvider.php`, `FeeType`, `Illuminate\Database\Eloquent\Factories\Factory`, `Illuminate\Database\Seeder`, `Livewire\Livewire`, `AddTeamMember.php`, `Laravel\Jetstream\Features`, `District`, `GovernmentDepartment`, `PatientTest`, `FeeCategory`, `PatientEmergencyTreatment`, `SpecialistFeesReportTest.php`, `Laravel\Fortify\Features`, `DeleteUser.php`, `PatientController.php`, `RemoveTeamMember.php`, `Illuminate\Auth\Access\Response`, `InviteTeamMember.php`, `Admission`?**
  _High betweenness centrality (0.140) - this node is a cross-community bridge._
- **Why does `Invoice` connect `Invoice` to `Chit`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Patient`, `PatientController.php`, `Controller`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `web.php`, `FeeType`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **Why does `Department` connect `Department` to `Chit`, `UpdateDepartmentRequest`, `Patient`, `SpecialistFeesReportTest.php`, `DepartmentController`, `PatientController.php`, `Controller`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `web.php`, `Illuminate\Database\Eloquent\Factories\Factory`, `FeeType`, `Illuminate\Database\Seeder`, `GovernmentDepartment`?**
  _High betweenness centrality (0.023) - this node is a cross-community bridge._
- **What connects `SITE_PATH`, `name`, `type` to the rest of the system?**
  _319 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `jquery-3.6.0.min.js` be split into smaller, more focused modules?**
  _Cohesion score 0.0526006464883926 - nodes in this community are weakly interconnected._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.09523809523809523 - nodes in this community are weakly interconnected._
- **Should `Chit` be split into smaller, more focused modules?**
  _Cohesion score 0.12903225806451613 - nodes in this community are weakly interconnected._