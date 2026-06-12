<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            // The primary key is the tenant slug (e.g. "acme"). See ProvisionTenant.
            $table->string('id')->primary();

            // Organization profile. These are declared as "custom columns" on the
            // App\Models\Tenant model so they are stored as real columns instead of
            // inside the virtual `data` JSON column below.
            $table->string('name');
            $table->string('plan')->default('free');

            // Cashier billing columns. The Tenant (organization) is the Billable
            // entity, so these live on the central `tenants` table — NOT on users,
            // and NOT in any tenant database.
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();

            // stancl stores any attribute NOT listed in Tenant::getCustomColumns()
            // inside this JSON column. Handy for arbitrary tenant metadata.
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
