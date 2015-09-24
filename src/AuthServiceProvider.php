<?php 

namespace Origami\Auth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Origami\Auth\Console\AuthSetupCommand;
use Origami\Auth\Console\AuthTablesCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;

class AuthServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->mergeConfigFrom(
            __DIR__.'/../config/permissions.php', 'permissions'
        );

		$this->registerConsoleCommands();

        $this->registerDrawbridge();
	}

    protected function registerDrawbridge()
    {
        $this->app->singleton(Drawbridge::class);

        $this->app->make(GateContract::class)->before(function ($user, $ability, $model = null, $additional = null) {
            if ( app(Drawbridge::class)->check($user, $ability, $model) )  {
                return true;
            }
        });
    }

    protected function registerConsoleCommands()
    {
        $this->app->singleton('command.auth.tables', function ($app) {
            return new AuthTablesCommand($app['files'], $app['composer']);
        });

        $this->commands('command.auth.tables');

        $this->app->singleton('command.auth.setup', function ($app) {
            return new AuthSetupCommand($app['config']);
        });

        $this->commands('command.auth.setup');
    }

	/**
     * Register any application permissions.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/permissions.php' => config_path('permissions.php'),
        ]);

        // Dynamically register permissions with Laravel's Gate.
//        foreach ($this->getPermissions() as $permission) {
//            $gate->define($permission->name, function ($user) use ($permission) {
//                return $user->hasPermission($permission);
//            });
//        }
    }

    /**
     * Fetch the collection of site permissions.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getPermissions()
    {
        if ( ! Schema::hasTable('roles') ) {
            return new Collection();
        }

        return Permission::with('roles')->get();
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['command.auth.tables', 'command.auth.setup'];
	}

}
