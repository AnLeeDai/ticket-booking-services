# Genre module notes (2026-02-05)

## Summary
- Added admin CRUD, toggle, search, pagination, and created-date filtering for genres.
- Added `active` enum to support hide/show behavior.
- Standardized JSON responses with `success`, `message`, `data`, and `meta` for paginated list.

## Files added
- app/Http/Controllers/Admin/GenreController.php
- app/Http/Requests/GenreStoreRequest.php
- app/Http/Requests/GenreUpdateRequest.php
- database/migrations/2026_02_05_000000_add_active_to_genres_table.php

## Files updated
- app/Models/Genre.php
- routes/api.php

## Routes (admin, auth:sanctum + role:admin)
- GET /api/admin/genres
- POST /api/admin/genres
- GET /api/admin/genres/{genre}
- PUT /api/admin/genres/{genre}
- PATCH /api/admin/genres/{genre}/toggle

## Notes
- Run: php artisan migrate
- Verify: php artisan route:list
- Commit message: feat: add admin genre module
