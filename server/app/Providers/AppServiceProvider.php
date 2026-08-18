<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\CustodyEvent;
use App\Models\Department;
use App\Models\Item;
use App\Models\Location;
use App\Models\Report;
use App\Models\ReturnRecord;
use App\Models\StorageLocation;
use App\Models\SystemAnnouncement;
use App\Models\SystemSetting;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use App\Policies\CampusPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ClaimPolicy;
use App\Policies\CustodyEventPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\ItemPolicy;
use App\Policies\LocationPolicy;
use App\Policies\ReportPolicy;
use App\Policies\ReturnPolicy;
use App\Policies\StorageLocationPolicy;
use App\Policies\SystemAnnouncementPolicy;
use App\Policies\SystemSettingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Item::class, ItemPolicy::class);
        Gate::policy(Claim::class, ClaimPolicy::class);
        Gate::policy(CustodyEvent::class, CustodyEventPolicy::class);
        Gate::policy(ReturnRecord::class, ReturnPolicy::class);
        Gate::policy(Campus::class, CampusPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(StorageLocation::class, StorageLocationPolicy::class);
        Gate::policy(SystemAnnouncement::class, SystemAnnouncementPolicy::class);
        Gate::policy(SystemSetting::class, SystemSettingPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
    }
}
