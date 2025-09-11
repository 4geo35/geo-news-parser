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
        Schema::create('geo_imports', function (Blueprint $table) {
            $table->uuid('id');
            $table->string("batch_id")->nullable();
            $table->string("url")->nullable();
            $table->string("page")->nullable();
            $table->string("paginator")->nullable();
            $table->unsignedInteger("first_page")->nullable();
            $table->unsignedInteger("last_page")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_imports');
    }
};
