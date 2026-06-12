<?php

namespace Database\Seeders;

use App\Actions\ProvisionTenant;
use App\Enums\Role;
use App\Models\Membership;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Central demo seeder. Creates a few placeholder users and two organizations,
 * each with its own provisioned database, then seeds demo projects into them.
 *
 * IMPORTANT: every credential here is an obvious placeholder. Do NOT add real
 * users, secrets, or client data — this seeder ships in a public repository.
 *
 *   owner@example.com  / password   (Owner of Acme, Member of Globex)
 *   admin@example.com  / password   (Admin of Acme, Owner of Globex)
 *   member@example.com / password   (Member of Acme)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $alice = $this->demoUser('Alice Owner', 'owner@example.com');
        $bob = $this->demoUser('Bob Admin', 'admin@example.com');
        $carol = $this->demoUser('Carol Member', 'member@example.com');

        $provisioner = app(ProvisionTenant::class);

        // Acme — owned by Alice, with Bob (admin) and Carol (member).
        $acme = $provisioner->create($alice, 'Acme Inc');
        Membership::create(['tenant_id' => $acme->id, 'user_id' => $bob->id, 'role' => Role::Admin->value]);
        Membership::create(['tenant_id' => $acme->id, 'user_id' => $carol->id, 'role' => Role::Member->value]);

        // Globex — owned by Bob, with Alice as a member (demonstrates multi-org users).
        $globex = $provisioner->create($bob, 'Globex Corp');
        Membership::create(['tenant_id' => $globex->id, 'user_id' => $alice->id, 'role' => Role::Member->value]);

        // Seed demo data into each tenant's own database.
        $this->seedTenant($acme);
        $this->seedTenant($globex);
    }

    private function demoUser(string $name, string $email): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }

    private function seedTenant(Tenant $tenant): void
    {
        $tenant->run(function () {
            (new TenantDatabaseSeeder)->run();
        });
    }
}
