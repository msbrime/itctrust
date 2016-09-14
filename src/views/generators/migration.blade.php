<?php echo '<?php' ?>

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ItctrustSetupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create table for storing roles
        Schema::create('{{ $rolesTable }}', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create table for storing mandates
        Schema::create('{{ $mandatesTable }}', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create table for associating roles to users (Many-to-Many)
        Schema::create('{{ $roleUserTable }}', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();

            $table->foreign('user_id')->references('{{ $userKeyName }}')->on('{{ $usersTable }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('{{ $rolesTable }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['user_id', 'role_id']);
        });

        // Create table for associating mandates to users (Many-to-Many)
        Schema::create('{{ $mandateUserTable }}', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('mandate_id')->unsigned();

            $table->foreign('user_id')->references('{{ $userKeyName }}')->on('{{ $usersTable }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('mandate_id')->references('id')->on('{{ $mandatesTable }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['user_id', 'mandate_id']);
        });

        // Create table for storing permissionSets
        Schema::create('{{ $permissionSetsTable }}', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });


        // Create table for associating roles to permissionSets
        Schema::create('{{ $permissionSetRoleTable }}', function (Blueprint $table) {
            $table->integer('permission_set_id')->unsigned();
            $table->integer('role_id')->unsigned();

            $table->foreign('permission_set_id')->references('id')->on('{{ $permissionSetsTable }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('{{ $rolesTable }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['permission_set_id', 'role_id']);
        });

        // Create table for associating mandates to permissionSets
        Schema::create('{{ $mandatePermissionSetTable }}', function (Blueprint $table) {
            
            $table->integer('permission_set_id')->unsigned();
            $table->integer('mandate_id')->unsigned();

            $table->foreign('permission_set_id')->references('id')->on('{{ $permissionSetsTable }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('mandate_id')->references('id')->on('{{ $mandatesTable }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['permission_set_id', 'mandate_id']);
        });


        // Create table for storing permissions
        Schema::create('{{ $permissionsTable }}', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });


        // Create table for associating permissions to permission sets (Many-to-Many)
        Schema::create('{{ $permissionPermissionSetTable }}', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('permission_set_id')->unsigned();

            $table->foreign('permission_id')->references('id')->on('{{ $permissionsTable }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('permission_set_id')->references('id')->on('{{ $permissionSetsTable }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['permission_id', 'permission_set_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('{{ $permissionPermissionSetTable }}');
        Schema::drop('{{ $permissionsTable }}');
        Schema::drop('{{ $mandatePermissionSetTable }}');
        Schema::drop('{{ $permissionSetRoleTable }}');
        Schema::drop('{{ $permissionSetsTable }}');
        Schema::drop('{{ $roleUserTable }}');
        Schema::drop('{{ $mandateUserTable }}');
        Schema::drop('{{ $rolesTable }}');
        Schema::drop('{{ $mandatesTable }}');
    }
}