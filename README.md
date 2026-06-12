# SaaS Starter Kit

> A Laravel multi-tenant SaaS starter kit with a **separate database per tenant** — plus teams, roles, invitations, and Stripe billing. Fork it and start shipping.

Most open-source SaaS starters isolate tenants with a `tenant_id` column and hope every query remembers to filter on it. This one gives **every organization its own database**, so isolation is physical: a forgotten `where` clause can't leak another tenant's data because the data isn't even in the same database.

Built on Laravel 12, stancl/tenancy, Laravel Cashier, Breeze (Blade), Tailwind CSS and Alpine.js.

---

## Features

- 🗄️ **Database-per-tenant multi-tenancy** (stancl/tenancy v3) — automatic database creation, migration and teardown per organization.
- 🏢 **Organizations** — register, switch between, and delete organizations. One organization = one tenant = one database.
- 👥 **Teams, roles & invitations** — `Owner` / `Admin` / `Member` roles on a central pivot, token-based email invitations with expiry.
- 🔐 **Authentication** — Laravel Breeze (Blade) with email verification and password reset.
- 💳 **Stripe billing** — Laravel Cashier subscriptions, free trials, and the hosted billing portal. The organization is the billable entity.
- ⚙️ **Per-tenant settings** — stored inside each tenant's own database.
- 🎨 **Tailwind UI** — clean dashboard, Blade + Alpine.js, light/dark mode.
- ✅ **Tested** — PHPUnit feature tests for tenancy isolation, provisioning, auth, invitations, RBAC and billing.

---

## Screenshots

> _Add your own screenshots here once running locally._

| Landing | Dashboard | Members | Billing |
| --- | --- | --- | --- |
| _(screenshot)_ | _(screenshot)_ | _(screenshot)_ | _(screenshot)_ |

---

## Requirements

- PHP **8.2+**
- MySQL 8 / MariaDB (multi-database tenancy uses real `CREATE DATABASE` statements)
- Composer, Node.js 18+

> The DB user must be allowed to create and drop databases, since each tenant gets its own.

---

## Installation

```bash
git clone https://github.com/your-org/saas-starter-kit.git
cd saas-starter-kit

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
```

Create the central database, then point `.env` at it (defaults shown):

```env
DB_CONNECTION=central
DB_DATABASE=saas_central
DB_USERNAME=root
DB_PASSWORD=
TENANT_IDENTIFICATION=path
```

```bash
# create the central database
mysql -u root -e "CREATE DATABASE saas_central"

# central tables + demo data (two organizations, each with its own database)
php artisan migrate --seed

php artisan serve
```

Visit `http://localhost:8000`. Demo logins (all password `password`):

| Email | Role |
| --- | --- |
| `owner@example.com` | Owner of Acme, Member of Globex |
| `admin@example.com` | Admin of Acme, Owner of Globex |
| `member@example.com` | Member of Acme |

After logging in you'll land inside an organization at `http://localhost:8000/acme-inc/dashboard`.

### Stripe (optional)

Billing works without keys (the page renders, checkout is disabled). To enable it, add your **test** keys and price IDs to `.env`:

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_PRICE_PRO=price_...
STRIPE_PRICE_BUSINESS=price_...
```

Cashier auto-registers the webhook at `POST /stripe/webhook`. Forward events locally with the Stripe CLI:

```bash
stripe listen --forward-to localhost:8000/stripe/webhook
```

### Running tests

Tenancy tests run against a real MySQL database. Create it once:

```bash
mysql -u root -e "CREATE DATABASE saas_central_testing"
php artisan test
```

---

## Architecture Decisions

### Why a database per tenant

The headline feature. A central database holds shared records — `users`, `tenants` (organizations), `memberships`, `invitations`, and all Stripe/Cashier billing tables. Every organization additionally gets its **own** database (`tenant_<slug>`) holding its application data (`projects`, `tenant_settings`, …).

The request lifecycle:

1. A tenant route is matched, e.g. `GET /acme-inc/dashboard` (path identification).
2. `InitializeTenancyByPath` resolves the `{tenant}` segment (`acme-inc`) to a `Tenant` and stancl swaps the **default database connection** to `tenant_acme-inc`.
3. `EnsureMemberOfTenant` confirms the authenticated user belongs to that organization.
4. From here, models like `Project` read and write the tenant's own database — no `tenant_id`, no global scope, no way to accidentally query across tenants.

Trade-off: more databases to run migrations against (`php artisan tenants:migrate`) and back up. In return you get the strongest isolation guarantee available, simple per-tenant backups/restores, and easy per-tenant data residency.

### Tenant = Organization

There is no separate "team inside a tenant" concept — one organization is one tenant is one database. This keeps the model and the docs simple. The `Tenant` model's primary key **is** its slug (e.g. `acme-inc`), which also names its database and appears in its URLs.

### Billing lives in the central database

The `Tenant` (organization) is the Cashier `Billable` model, and all billing routes run in the **central** context — tenancy is never initialized for them. Invoking Cashier while a tenant connection is active is a known stancl/tenancy pitfall, and billing must remain queryable even if a tenant database is dropped. So Cashier's tables and the billable columns live on the central `tenants` table.

### Roles in the central database

`Owner` / `Admin` / `Member` are stored on the central `memberships` pivot, so access control survives independently of any tenant database and a single user can belong to many organizations with different roles. Enforcement is layered: `EnsureRole` middleware for coarse gating, policies for per-action checks, and a `Gate::before` hook that lets an organization's owner pass every check.

### Path-based tenant identification by default

`/{tenant}/…` works on plain `localhost` with zero DNS/wildcard setup — ideal for local dev. Switch to subdomain or domain identification by changing `TENANT_IDENTIFICATION` and the middleware in `routes/tenant.php`.

### Cache & queue

stancl's cache-tenancy bootstrapper scopes the cache with tags, which the default `database`/`file` cache stores don't support — so it's disabled by default and cache stays central (per-tenant data is already isolated at the database level). Enable it in `config/tenancy.php` if you use a taggable store such as Redis. Sessions and the database cache are pinned to the central connection so they keep working after tenancy swaps the default connection.

### Auth: Breeze (Blade)

Laravel 13 moved its first-party auth to Fortify and dropped the Blade starter kit, but Breeze remains the cleanest way to get a pure Blade + Tailwind + Alpine auth scaffold, so we use it here. The architecture is auth-agnostic — swap in Fortify + custom Blade views if you prefer.

---

## Why this starter kit

- **Real isolation, not a column.** Database-per-tenant is the differentiator. If you're building something where a cross-tenant data leak is unacceptable, column scoping is a footgun; separate databases are not.
- **Lean and readable.** No product-specific cruft, no kitchen-sink dependencies. Every non-obvious decision is commented in the code and explained above.
- **The hard parts are done.** Tenant provisioning, connection switching, billing-in-a-multi-DB-world, role enforcement, and invitations are wired up and tested — the things that are fiddly to get right the first time.

---

## Project structure

```
app/
  Actions/            ProvisionTenant, AcceptInvitation
  Enums/Role.php
  Http/Controllers/   Central/* (org, members, invitations, billing)  Tenant/* (dashboard, projects, settings)
  Http/Middleware/    EnsureMemberOfTenant, EnsureRole
  Models/             User, Tenant (Billable), Membership, Invitation, Tenant/{Project, TenantSetting}
  Policies/TenantPolicy.php
  Providers/TenancyServiceProvider.php   (database create/migrate/delete pipeline)
config/               tenancy.php, plans.php, cashier.php
database/
  migrations/         central tables
  migrations/tenant/  per-tenant tables
  seeders/            DatabaseSeeder (central), TenantDatabaseSeeder
routes/               web.php, central.php (billing here), tenant.php
tests/Feature/        Tenancy, Teams, Billing (+ Breeze Auth)
```

---

## License

[MIT](LICENSE).
