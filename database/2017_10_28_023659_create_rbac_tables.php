<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRbacTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::defaultStringLength(191);

        Schema::create(config('rbac.table.roles'), function (Blueprint $table) {

            $table->increments('id');
            $table->string('name', 50)->unique();
            $table->string('slug', 50);
            $table->timestamps();
        });

        Schema::create(config('rbac.table.permissions'), function (Blueprint $table) {

            $table->increments('id');
            $table->string('name', 190)->unique();
            $table->string('slug', 128);
            $table->string('http_method')->nullable();
            $table->text('http_path')->nullable();
            $table->timestamps();

        });

        Schema::create(config('rbac.table.role_users'), function (Blueprint $table){

            $table->integer(config('rbac.constraint.role_users.role_id'), false, true);
            $table->integer(config('rbac.constraint.role_users.user_id'), false, true);
            $table->unique([config('rbac.constraint.role_users.role_id'),
                config('rbac.constraint.role_users.user_id')]);
            $table->timestamps();

            $table->foreign(config('rbac.constraint.role_users.user_id'))->references('id')
                  ->on(config('rbac.table.users'))->onDelete('cascade');

            $table->foreign(config('rbac.constraint.role_users.role_id'))->references('id')
                  ->on(config('rbac.table.roles'))->onDelete('cascade');
        });

        Schema::create(config('rbac.table.role_permissions'), function (Blueprint $table) {

            $table->integer(config('rbac.constraint.role_permissions.role_id'), false, true);
            $table->integer(config('rbac.constraint.role_permissions.permission_id'), false, true);
            $table->unique([config('rbac.constraint.role_permissions.role_id'),
                config('rbac.constraint.role_permissions.permission_id')]);
            $table->timestamps();

            $table->foreign(config('rbac.constraint.role_permissions.permission_id'))->references('id')
                  ->on(config('rbac.table.permissions'))->onDelete('cascade');
            $table->foreign(config('rbac.constraint.role_permissions.role_id'))->references('id')
                  ->on(config('rbac.table.roles'))->onDelete('cascade');


        });

        Schema::create(config('rbac.table.user_permissions'), function (Blueprint $table){

            $table->integer(config('rbac.constraint.user_permissions.user_id'), false, true);
            $table->integer(config('rbac.constraint.user_permissions.permission_id'), false, true);
            $table->unique([config('rbac.constraint.user_permissions.user_id'),
                config('rbac.constraint.user_permissions.permission_id')]);
            $table->timestamps();

            $table->foreign(config('rbac.constraint.user_permissions.user_id'))->references('id')
                  ->on(config('rbac.table.users'))->onDelete('cascade');
            $table->foreign(config('rbac.constraint.user_permissions.permission_id'))->references('id')
                  ->on(config('rbac.table.permissions'))->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('rbac.table.user_permissions'));
        Schema::dropIfExists(config('rbac.table.role_permissions'));
        Schema::dropIfExists(config('rbac.table.role_users'));
        Schema::dropIfExists(config('rbac.table.permissions'));
        Schema::dropIfExists(config('rbac.table.roles'));
    }
}
