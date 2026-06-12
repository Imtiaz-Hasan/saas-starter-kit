<?php

namespace Database\Seeders;

use App\Models\Tenant\Project;
use Illuminate\Database\Seeder;

/**
 * Seeds demo data INSIDE a tenant database. Run within a tenant context, e.g.
 * via $tenant->run(fn () => (new TenantDatabaseSeeder)->run()) or
 * `php artisan tenants:seed`. All data here is placeholder content only.
 */
class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $demo = [
            ['name' => 'Website redesign', 'status' => 'active'],
            ['name' => 'Mobile app', 'status' => 'active'],
            ['name' => 'Q3 marketing', 'status' => 'archived'],
        ];

        foreach ($demo as $project) {
            Project::create([
                'name' => $project['name'],
                'description' => 'Demo project — replace with your own data.',
                'status' => $project['status'],
            ]);
        }
    }
}
