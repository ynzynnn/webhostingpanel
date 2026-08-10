<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('databases', function (Blueprint $table) {
            if (! Schema::hasColumn('databases', 'website_id')) {
                $table->foreignId('website_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
            }
            if (! Schema::hasColumn('databases', 'name')) {
                $table->string('name')->nullable()->after('website_id');
            }
            if (! Schema::hasColumn('databases', 'username')) {
                $table->string('username')->nullable()->after('name');
            }
            if (! Schema::hasColumn('databases', 'host')) {
                $table->string('host')->default('127.0.0.1')->after('username');
            }
            if (! Schema::hasColumn('databases', 'port')) {
                $table->integer('port')->default(3306)->after('host');
            }
            if (Schema::hasColumn('databases', 'db_name')) {
                $table->string('db_name')->nullable()->change();
            }
            if (Schema::hasColumn('databases', 'db_user')) {
                $table->string('db_user')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('databases', function (Blueprint $table) {
            //
        });
    }
};
