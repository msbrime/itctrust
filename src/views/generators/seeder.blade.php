<?php echo '<?php' ?>


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItctrustSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->truncateItctrustTables();
        
        $config = config('itctrust_seeder.role_structure');
        $permSetStructure = collect(config('itctrust_seeder.permission_set_structure'));
        $mapPermission = collect(config('itctrust_seeder.permissions_map'));

        foreach ($config as $key => $modules) {
            // Create a new role
            $role = \{{ $role }}::create([
                'name' => $key,
                'display_name' => ucfirst($key),
                'description' => ucfirst($key)
            ]);

            $this->command->info('Creating Role '. strtoupper($key));

            // Reading role permission modules
            foreach ($modules as $module => $value) {
                $permSet =  $value;


                    $newPermSet = \{{ $permissionSet }}::firstOrCreate([
                        'name' => $permSet,
                        'display_name' => ucwords(str_replace("_"," ",$permSet)),
                        'description' => ucwords(str_replace("_"," ",$permSet)). " related permissions",
                    ]);

                    $permSetPermissions = $permSetStructure->get($permSet);

                    // Reading role permission modules
                    foreach ($permSetPermissions as $permName => $permValue) {
                        $permissions = explode(',', $permValue);

                        foreach ($permissions as $p => $perm) {
                            $permissionValue = $mapPermission->get($perm);

                            $permit = \{{ $permission }}::firstOrCreate([
                                'name' => $permName . '-' . $permissionValue,
                                'display_name' => ucfirst($permissionValue) . ' ' . ucfirst($permName),
                                'description' => ucfirst($permissionValue) . ' ' . ucfirst($permName),
                            ]);

                            $this->command->info('Creating Permission to '.$permissionValue.' for '. $permName);
                            
                            if (!$newPermSet->hasPermission($permit->name)) {
                                $newPermSet->attachPermission($permit);
                            } else {
                                $this->command->info( $permissionValue . ' already exist');
                            }
                        }
                    }

                    $this->command->info('Creating PermissionSet ' . $permSet);
                    
                    if (!$role->hasPermissionSet($newPermSet->name)) {
                        $role->attachPermissionSet($newPermSet);
                    } else {
                        $this->command->info($permSet . ' already exist');
                    }
            }

            // Create default user for each role
            $user = \{{ $user }}::create([
                'name' => ucfirst($key),
                'email' => $key.'@app.com',
                'password' => bcrypt('password'),
                'remember_token' => str_random(10),
            ]);
            $user->attachRole($role);
        }
    }

    /**
     * Truncates all the itctrust tables and the users table
     * @return  void
     */
    public function truncateItctrustTables()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('permission_permission_set')->truncate();
        DB::table('permission_set_role')->truncate();
        DB::table('role_user')->truncate();
        \App\User::truncate();
        \App\Role::truncate();
        \App\PermissionSet::truncate();
        \App\Permission::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
