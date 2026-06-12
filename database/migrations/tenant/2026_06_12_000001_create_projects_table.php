<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TENANT migration — runs inside each tenant's own database (tenant_<slug>).
 *
 * `projects` is the placeholder "feature" table that proves data isolation:
 * a project created in tenant_acme physically cannot be seen from tenant_globex
 * because it lives in a different database. Replace this with your real domain
 * tables and you have a multi-tenant SaaS.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active | archived
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
