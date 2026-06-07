# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Setup
```bash
composer setup   # Full setup: install deps, generate key, migrate, build frontend
```

### Development
```bash
composer dev     # Runs Laravel server + queue listener + log tailing + Vite concurrently
```
Or individually:
```bash
php artisan serve
npm run dev
php artisan queue:listen
```

### Build
```bash
npm run build    # Production Vite build
```

### Testing
```bash
composer test             # Run all PHPUnit tests
php artisan test          # Equivalent alias
php artisan test --filter TestName   # Run a single test
```

### Code Style
```bash
php artisan pint          # Laravel Pint (PHP code formatter)
```

### Database
```bash
php artisan migrate
php artisan migrate:fresh --seed
```

## Architecture

**Stack:** Laravel 12 (PHP 8.2+) + Vue 3 + Inertia.js + Tailwind CSS + AdminLTE 4, built with Vite.

### Request Flow
HTTP Request → `routes/web.php` (RESTful resource routes) → Controller → Eloquent Model → `Inertia::render('PageName', $data)` → Vue component in `resources/js/Pages/` receives data as props.

Form submissions use `router.post/put/delete` from `@inertiajs/vue3` — no separate API layer.

### Key Directories
- `app/Http/Controllers/` — One controller per domain entity (User, Departement, Poste, Affectation, Conge, Paiement, Transaction, Suivie, Role)
- `app/Models/` — Eloquent models; `User` uses UUID as primary key
- `resources/js/Pages/` — Full-page Vue components (one directory per entity, with Index/Create/Edit views)
- `resources/js/Components/` — Reusable Vue components (UI/, form inputs, modals)
- `resources/js/Layouts/` — `AuthenticatedLayout.vue` (dashboard shell), `GuestLayout.vue` (auth pages)
- `database/migrations/` — 15+ migrations defining the full schema

### Domain Model
The app is an HR/ERM system (EdemERM) with these core relationships:
- **User** (employee) ↔ **Role** (many-to-many via `role_user` pivot)
- **Departement** → **Poste** (job positions) → **Affectation** (assigns a User to a Poste)
- **User** → **Conge** (leave requests), **Paiement** (salary payments), **Suivie** (supervision tracking)
- **Departement** → **Transaction** (financial transactions)
- **Conge/Suivie** reference **Motif** and **TypeSuivie** lookup tables

### Frontend Conventions
- Pages use `defineProps` to receive Inertia-injected server data
- Shared layout is applied via `layout` option in `<script setup>` or via persistent layouts
- Tailwind is the primary styling utility; AdminLTE 4 provides the dashboard chrome
