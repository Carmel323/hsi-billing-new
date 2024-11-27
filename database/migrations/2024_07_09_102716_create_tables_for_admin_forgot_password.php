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
        if (!Schema::hasTable('password_tokens_admin')) {

            Schema::create('password_tokens_admin', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('password_token');
                $table->timestamps();
            });
        }

        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'admin_last_logged_in')) {
                $table->string('admin_last_logged_in')->nullable()->after('receive_mails');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_tokens_admin');

        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'admin_last_logged_in')) {
                $table->dropColumn('admin_last_logged_in');
            }
        });
    }
};
