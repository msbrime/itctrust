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

class MigrationCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'itctrust:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a migration following the Itctrust specifications.';

    /**
     * Suffix of the migration name.
     *
     * @var string
     */
    protected $migrationSuffix = 'itctrust_setup_tables';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->laravel->view->addNamespace('itctrust', substr(__DIR__, 0, -8).'views');

        $rolesTable                   = Config::get('itctrust.roles_table');
        $roleUserTable                = Config::get('itctrust.role_user_table');
        $mandatesTable                = Config::get('itctrust.mandates_table');
        $mandateUserTable             = Config::get('itctrust.mandate_user');
        $permissionSetsTable          = Config::get('itctrust.permission_sets_table');
        $permissionSetRoleTable       = Config::get('itctrust.permission_set_role_table');
        $mandatePermissionSetTable    = Config::get('itctrust.mandate_permission_set_table');
        $permissionsTable             = Config::get('itctrust.permissions_table');
        $permissionPermissionSetTable = Config::get('itctrust.permission_permission_set_table');

        $this->line('');
        $this->info("Tables: $rolesTable, $roleUserTable, $mandatesTable, $mandateUserTable, $permissionSetsTable, $permissionSetRoleTable,$mandatePermissionSetTable, $permissionsTable, $permissionPermissionSetTable");

        $message = $this->generateMigrationMessage(
            $rolesTable, 
            $roleUserTable, 
            $mandatesTable, 
            $mandateUserTable, 
            $permissionSetsTable, 
            $permissionSetRoleTable,
            $mandatePermissionSetTable, 
            $permissionsTable, 
            $permissionPermissionSetTable
        );

        $this->comment($message);

        $existingMigrations = $this->alreadyExistingMigrations();

        if ($existingMigrations) {
            $this->line('');
            
            $this->warn($this->getExistingMigrationsWarning($existingMigrations));
        }

        $this->line('');

        if (! $this->confirm("Proceed with the migration creation?", "yes")) {
            return;
        }

        $this->line('');

        $this->info("Creating migration...");

        if ($this->createMigration($rolesTable, $roleUserTable, $mandatesTable, $mandateUserTable, $permissionSetsTable, $permissionSetRoleTable,$mandatePermissionSetTable ,$permissionsTable, $permissionPermissionSetTable)) {
            $this->info("Migration successfully created!");
        } else {
            $this->error(
                "Couldn't create migration.\n".
                "Check the write permissions within the database/migrations directory."
            );
        }

        $this->line('');
    }

    /**
     * Create the migration.
     *
     * @param  string $rolesTable
     * @param  string $roleUserTable
     * @param  string $permissionsTable
     * @param  string $permissionRoleTable
     * @return bool
     */
    protected function createMigration($rolesTable, $roleUserTable, $mandatesTable, $mandateUserTable, $permissionSetsTable, $permissionSetRoleTable,$mandatePermissionSetTable, $permissionsTable, $permissionPermissionSetTable)
    {
        $migrationPath = $this->getMigrationPath();

        $userModel   = Config::get('auth.providers.users.model');
        $user = new $userModel;
        $userKeyName = $user->getKeyName();
        $usersTable = $user->getTable();

        $data = compact(
            'rolesTable',
            'roleUserTable',
            'mandatesTable', 
            'mandateUserTable',
            'permissionsTable',
            'permissionSetsTable',
            'permissionSetRoleTable',
            'permissionPermissionSetTable',
            'usersTable',
            'userKeyName'
        );

        $output = $this->laravel->view->make('itctrust::generators.migration')->with($data)->render();

        if (!file_exists($migrationPath) && $fs = fopen($migrationPath, 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }

        return false;
    }

    /**
     * Generate the message to display when running the
     * console command showing what tables are going
     * to be created.
     *
     * @param  string $rolesTable
     * @param  string $roleUserTable
     * @param  string $permissionsTable
     * @param  string $permissionRoleTable
     * @return string
     */
    protected function generateMigrationMessage($rolesTable, $roleUserTable, $mandatesTable, $mandateUserTable, $permissionSetsTable, $permissionSetRoleTable,$mandatePermissionSetTable, $permissionsTable, $permissionPermissionSetTable)
    {
        return "A migration that creates '$rolesTable', '$roleUserTable', '$permissionsTable','$permissionSetsTable','$permissionSetRoleTable','$permissionPermissionSetTable'".
            " tables will be created in database/migrations directory";
    }

    /**
     * Build a warning regarding possible duplication
     * due to already existing migrations
     *
     * @param  array $existingMigrations
     * @return string
     */
    protected function getExistingMigrationsWarning(array $existingMigrations)
    {
        if (count($existingMigrations) > 1) {
            $base = "Itctrust migrations already exist.\nFollowing files were found: ";
        } else {
            $base = "Itctrust migration already exists.\nFollowing file was found: ";
        }

        return $base . array_reduce($existingMigrations, function ($carry, $fileName) {
            return $carry . "\n - " . $fileName;
        });
    }

    /**
     * Check if there is another migration
     * with the same suffix.
     *
     * @return array
     */
    protected function alreadyExistingMigrations()
    {
        $matchingFiles = glob($this->getMigrationPath('*'));

        return array_map(function ($path) {
            return basename($path);
        }, $matchingFiles);
    }

    /**
     * Get the migration path.
     *
     * The date parameter is optional for ability
     * to provide a custom value or a wildcard.
     *
     * @param  string|null $date
     * @return string
     */
    protected function getMigrationPath($date = null)
    {
        $date = $date ?: date('Y_m_d_His');

        return database_path("migrations/${date}_{$this->migrationSuffix}.php");
    }
}
