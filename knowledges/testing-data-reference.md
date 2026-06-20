# Testing Data Management

## Overview
This file tracks temporary test data created for manual UI exploration and verification.

## Seeder Information
- **Seeder File:** `database/seeders/TestingDataSeeder.php`
- **Purpose:** Populate rich, fake data to manually verify UI interactions (clicking, navigation, forms).
- **Data Included:** 
    - 1 Primary Test User (`test@example.com`)
    - 5 Additional Test Users
    - Posts, Categories, Tags, Comments, Ratings, Reactions, and Reports.
- **Constraints:**
  - **Excluded Tables:** `fonts`, `themes` (or color-related tables as requested).

## How to Run
To seed the database with this interactive data, run:
```bash
php artisan db:seed --class=TestingDataSeeder
```

## How to Clean Up
1. **Remove the Seeder:**
   ```bash
   rm database/seeders/TestingDataSeeder.php
   ```
2. **Clear the Database:**
   If you wish to remove all data and reset to a clean state:
   ```bash
   php artisan migrate:fresh
   ```
   *Note: Ensure you have your database backups if this is a development environment with other important data.*
