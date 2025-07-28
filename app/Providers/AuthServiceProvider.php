<?php

namespace App\Providers;

use App\Models\DailyReport;      // <-- Tambahkan ini
use App\Policies\DailyReportPolicy;  // <-- Tambahkan ini
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Daftarkan policy di sini
        DailyReport::class => DailyReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}