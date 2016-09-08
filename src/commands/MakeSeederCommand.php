<?php

namespace Itctrust;

/**
 * This file is part of Itctrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Itctrust
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class MakeSeederCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'itctrust:seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the seeder following the Itctrust specifications.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->laravel->view->addNamespace('itctrust', substr(__DIR__, 0, -8).'views');

        if ($this->createSeeder()) {
            $this->info("Seeder successfully created!");
        } else {
            $this->error(
                "Couldn't create seeder.\n".
                "Check the write permissions within the database/seeds directory."
            );
        }

        $this->line('');
    }

    /**
     * Create the seeder
     * @return bool
     */
    protected function createSeeder()
    {
        $permission = Config::get('itctrust.permission', 'App\Permission');
        $permissionSet = Config::get('itctrust.permission_set', 'App\PermissionSet');
        $role = Config::get('itctrust.role', 'App\Role');
        $permissionPermissionSets = Config::get('itctrust.permission_permission_set_table');
        $rolePermissionSets = Config::get('itctrust.permission_set_role_table');
        $roleUsers = Config::get('itctrust.role_user_table');
        $user   = Config::get('auth.providers.users.model', 'App\User');

        $migrationPath = $this->getMigrationPath();
        $output = $this->laravel->view->make('itctrust::generators.seeder')
            ->with(compact([
                'role',
                'permission',
                'permissionSet',
                'permissionPermissionSets',
                'user',
                'rolePermissionSets',
                'roleUsers',
            ]))
            ->render();

        if (!file_exists($migrationPath) && $fs = fopen($migrationPath, 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }

        return false;
    }

    /**
     * Get the seeder path.
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        return database_path("seeds/ItctrustSeeder.php");
    }
}
