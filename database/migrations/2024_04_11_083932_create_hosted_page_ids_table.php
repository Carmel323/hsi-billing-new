<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        if (!Schema::hasTable('hosted_page_ids')) {
            
            Schema::create('hosted_page_ids', function (Blueprint $table) {
                $table->id();
                $table->string('hostedpage_id');
                $table->timestamps();
            });
        }
    }

   
    public function down(): void
    {
        Schema::dropIfExists('hosted_page_ids');
    }
};
