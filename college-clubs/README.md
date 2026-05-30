# College Clubs & Events Portal

A premium, production-ready Laravel application scaffolded to coordinate college student clubs, schedule events, process registrations, compile financial proceeds, and expose REST JSON endpoints for mobile (Flutter) client consumption.

## Architecture Highlights
- **Role-based Scope Isolation:** Built-in middleware (`RoleMiddleware` and `ScopeToClub`) protects routes so System Admins can supervise the global network while Club Presidents remain entirely restricted to their assigned club's statistics, event CRUDs, and monthly/yearly performance metrics.
- **REST API + Sanctum:** Complete token authentication endpoints and controller actions designed to interact with a Flutter mobile client (students can browse clubs, view upcoming events, check ticket pricing, spots remaining, register, and review history).
- **SQLite Out-Of-The-Box:** Scaffolds are pre-configured to run with a localized SQLite engine to ensure friction-free database creation.
- **Vibrant Glassmorphic Aesthetics:** Layout dashboards, login cards, graphs, and breakdown lists utilize Inter/Outfit modern typography, Tailwind CSS, and custom styling to offer a premium UX.

---

## Folder Map & Completed Scaffolds

```text
college-clubs/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── LoginController.php        # Web session authentication & role redirect
│   │   │   ├── Admin/
│   │   │   │   ├── AdminController.php        # Global system metrics & recent logs
│   │   │   │   └── ClubManagerController.php   # Manage clubs list & assign presidents
│   │   │   ├── President/
│   │   │   │   ├── DashboardController.php    # Scoped dashboard metrics for a club
│   │   │   │   ├── EventController.php        # Full Event CRUD + media upload logic
│   │   │   │   └── ReportController.php       # Grouped monthly/yearly registration reports
│   │   │   └── Api/
│   │   │       ├── AuthController.php         # Sanctum token endpoints (POST /api/login)
│   │   │       ├── ClubController.php         # GET /api/clubs & individual club details
│   │   │       ├── EventController.php        # GET /api/events with search & filters
│   │   │       └── RegistrationController.php # POST /api/events/{id}/register
│   │   └── Middleware/
│   │       ├── RoleMiddleware.php             # Validate 'admin', 'president', 'student'
│   │       └── ScopeToClub.php                # Block presidents from cross-club data
│   └── Models/
│       ├── User.php                           # HasApiTokens, belongs to Club
│       ├── Club.php                           # Linked to President User & Events
│       ├── Event.php                          # Capacity checks, spots tracking relations
│       ├── EventImage.php                     # Multiple banners per event
│       ├── Registration.php                   # Links student to event, maps Payment
│       └── Payment.php                        # Tracks registration fees
├── database/
│   ├── migrations/                            # Full relational schema migrations (8 files)
│   └── seeders/
│       ├── DatabaseSeeder.php                 # Master seeder calls
│       ├── UserSeeder.php                     # 1 Admin, 20 Presidents, 20 Clubs, 10 Students
│       └── EventsFromJsonSeeder.php           # Reads mock events.json into SQL tables
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php                      # Glassmorphic base layout with alerts
│   ├── auth/
│   │   └── login.blade.php                    # Beautiful dark-glowing login interface
│   ├── president/
│   │   ├── dashboard.blade.php                # Club-level metrics panel
│   │   ├── reports.blade.php                  # Interactive monthly progress bars
│   │   └── events/
│   │       ├── index.blade.php                # Cards-based event panel
│   │       └── create.blade.php               # Multi-column create/edit event form
│   └── admin/
│       ├── dashboard.blade.php                # Admin overview metrics
│       └── clubs.blade.php                    # On-the-fly onboarding views
├── routes/
│   ├── web.php                                # Web/Blade portal routes
│   └── api.php                                # Flutter client endpoints (Sanctum)
├── public/storage/                            # Initialized folder for media assets
└── events.json                                # Detailed college club event fixtures
```

---

## Local Setup & Deployment Guide

Follow these steps once you have PHP (>= 8.2) and Composer installed on your system:

### 1. Vendor Packages & Environment
Open your terminal inside the `college-clubs` directory:
```bash
# Install PHP Composer dependencies
composer install

# Install Vite & Asset dependencies
npm install

# Build asset bundle
npm run build
```

### 2. Configure Local Database
The application is pre-configured to use **SQLite** which requires no local database server (like MySQL):
```bash
# Create empty SQLite database file
# On Windows PowerShell:
New-Item -Path database -Name database.sqlite -ItemType File

# On macOS / Linux:
touch database/database.sqlite
```

Verify your `.env` contains the SQLite configurations (we have already created the `.env` with these defaults):
```env
DB_CONNECTION=sqlite
```

### 3. Key Generation & Seed Data
```bash
# Generate the application encryption key
php artisan key:generate

# Run schema migrations and populate data
php artisan migrate:fresh --seed
```
*Note: The `--seed` command triggers the `UserSeeder` and `EventsFromJsonSeeder`, parsing `events.json` and seeding the databases!*

### 4. Link Storage Assets
To serve uploaded event images:
```bash
php artisan storage:link
```

### 5. Launch Servers
Start both the PHP development server and Vite asset bundler:
```bash
# Run PHP server (available at http://localhost:8000)
php artisan serve
```

---

## Testing Credentials

Sign in on the web portal (`http://localhost:8000/login`) using the following accounts seeded by `UserSeeder`:

- **System Administrator:**
  - **Email:** `admin@college.edu`
  - **Password:** `password`
- **Coding Club President:**
  - **Email:** `president1@college.edu`
  - **Password:** `password`
- **Robotics Club President:**
  - **Email:** `president2@college.edu`
  - **Password:** `password`
- **Music Society President:**
  - **Email:** `president3@college.edu`
  - **Password:** `password`
- *(Presidents are seeded up to `president20@college.edu` for all 20 corresponding clubs)*
