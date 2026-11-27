<?php

namespace App\Support;

/**
 * Permissions that switch parts of the management dashboard on or off per user or role.
 * Administrator and Super-Admin hold all of them, so they keep the full dashboard; a user without
 * FILTERS only ever sees today.
 */
class DashboardPermissions
{
    public const FILTERS = 'view dashboard filters';

    /** @var array<string, string> section or chart key => permission name */
    public const CHARTS = [
        'highlights' => 'view dashboard highlights',
        'kpis' => 'view dashboard key numbers',
        'insights' => 'view dashboard insights',
        'daily_chits' => 'view dashboard daily chits chart',
        'daily_revenue' => 'view dashboard daily revenue chart',
        'race' => 'view dashboard revenue race chart',
        'mix' => 'view dashboard entitled vs paid chart',
        'control' => 'view dashboard control chart',
        'split' => 'view dashboard government vs hif share chart',
        'months' => 'view dashboard last 12 months chart',
        'yoy' => 'view dashboard year on year revenue chart',
        'services' => 'view dashboard services revenue chart',
        'department_revenue' => 'view dashboard department revenue chart',
        'heat_map' => 'view dashboard heat map chart',
        'hours' => 'view dashboard busiest hours chart',
        'weekdays' => 'view dashboard busiest weekdays chart',
        'weekday_variation' => 'view dashboard weekday variation chart',
        'day_of_month' => 'view dashboard day of month chart',
        'opd_departments' => 'view dashboard opd by department chart',
        'specialist' => 'view dashboard specialist vs general chart',
        'categories' => 'view dashboard service categories chart',
        'operation_theatre' => 'view dashboard operation theatre chart',
        'radiology' => 'view dashboard radiology chart',
        'employers' => 'view dashboard employers chart',
        'gender' => 'view dashboard gender chart',
        'age_pyramid' => 'view dashboard age pyramid chart',
        'admissions' => 'view dashboard admissions chart',
        'wards' => 'view dashboard wards chart',
        'projection' => 'view dashboard month projection',
        'team' => 'view dashboard team performance',
        'unusual_days' => 'view dashboard unusual days',
    ];

    /**
     * Every permission defined here.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::FILTERS, ...array_values(self::CHARTS)];
    }
}
