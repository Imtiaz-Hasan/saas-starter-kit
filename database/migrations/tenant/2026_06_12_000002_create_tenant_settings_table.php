<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TENANT migration — per-tenant settings as a simple key/value store inside the
 * tenant database. Because it lives in the tenant DB, settings are isolated by
 * construction; no tenant_id column or global scope is required.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
