<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use TomatoPHP\FilamentUsers\Resources\UserResource\Table\UserActions;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use App\Policies\ActivityPolicy;
use App\Policies\QueueMonitorPolicy;
use Croustibat\FilamentJobsMonitor\Models\QueueMonitor;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;
use Spatie\Health\Facades\Health;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\Vite;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::useCspNonce();

        if(config('app.redirect_https')){
            \URL::forceScheme('https');
        }

        FilamentColor::register([
            // 'gray' => Color::Gray,
            'slate' => Color::Slate,
            'zinc' => Color::Zinc,
            'neutral' => Color::Neutral,
            'stone' => Color::Stone,
            'red' => Color::Red,
            'orange' => Color::Orange,
            'amber' => Color::Amber,
            'yellow' => Color::Yellow,
            'lime' => Color::Lime,
            'green' => Color::Green,
            'emerald' => Color::Emerald,
            'teal' => Color::Teal,
            'cyan' => Color::Cyan,
            'sky' => Color::Sky,
            'blue' => Color::Blue,
            'indigo' => Color::Indigo,
            'violet' => Color::Violet,
            'purple' => Color::Purple,
            'fuchsia' => Color::Fuchsia,
            'pink' => Color::Pink,
            'rose' => Color::Rose,
        ]);

        Model::unguard();

        UserActions::register([
            ActivityLogTimelineTableAction::make('Activities'),
        ]);

        Gate::policy(Activity::class, ActivityPolicy::class);

        Health::checks([
            OptimizedAppCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
            DatabaseCheck::new(),
            UsedDiskSpaceCheck::new(),
            ScheduleCheck::new(),
        ]);

    }
}
