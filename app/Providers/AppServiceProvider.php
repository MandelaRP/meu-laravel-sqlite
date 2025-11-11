<?php

declare(strict_types = 1);

namespace App\Providers;

use App\Enums\Can;
use App\Models\SystemSetting;
use App\Models\User;
use App\Observers\SystemSettingObserver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

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
        $this->configModels();
        $this->setupLogViewer();
        $this->configCommands();
        $this->configUrl();
        $this->configDate();
        $this->configGates();
        $this->registerObservers();
    }

    private function registerObservers(): void
    {
        SystemSetting::observe(SystemSettingObserver::class);
    }

    private function setupLogViewer(): void
    {
        // Habilita o LogViewer para o usuário com email wender_dev@hotmail.com
        LogViewer::auth(fn ($request): bool => $request->user()
            && $request->user()->email == 'wender_dev@hotmail.com');
    }

    private function configModels(): void
    {
        // Desabilita a proteção de mass assignment. fillables não são necessários.
        Model::unguard();

        // Habilita o modo estrito para os modelos. garante que todos os atributos sejam definidos.
        Model::shouldBeStrict(true);
        
        // Desabilita prevenção de lazy loading para evitar erros em relacionamentos
        Model::preventLazyLoading(!app()->isProduction());
    }

    private function configCommands(): void
    {
        // Proíbe a execução de comandos destrutivos em produção.
        DB::prohibitDestructiveCommands(
            app()->environment('production')
        );
    }

    private function configUrl(): void
    {
        // Força o uso de HTTPS apenas em produção.
        if (app()->environment('production')) {
            URL::forceHttps();
        }
    }

    private function configDate(): void
    {
        // Define o formato de data para o CarbonImmutable. evita o problema de mutação de datas.
        Date::use(CarbonImmutable::class);
    }

    private function configGates(): void
    {
        foreach (Can::cases() as $permission) {
            Gate::define($permission->value, fn (User $user) => $user->permissions->contains('name', $permission->value));
        }
    }
}
