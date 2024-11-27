<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {   
        if (!Schema::hasTable('admins')) {   
            
            Schema::create('admins', function (Blueprint $table) {
                $table->id();
                $table->string('admin_name');
                $table->string('email');
                $table->string('password');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
