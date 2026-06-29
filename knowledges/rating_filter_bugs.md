# Rating Filter Bug Analysis

## Files Involved

| File | Role |
|------|------|
| [home.blade.php](../resources/views/layout/home.blade.php) | Alpine.js controller, filter state, `getFilterUrl()`, `toggleFilter()`, `init()` |
| [home-aside.blade.php](../resources/views/components/layout/home-aside.blade.php) | Rating checkbox UI (both sidebar & topbar variants) |
| [HomeController.php](../app/Http/Controllers/HomeController.php) | Server-side rating filtering query |

---

## Bug 1 — Type Mismatch: String vs. Integer in `filters.rating`

### Location
`home-aside.blade.php` L110, L222 and `home.blade.php` L250–252

### Problem
The rating checkboxes pass **string** values (`'5'`, `'4'`, etc.) via `toggleFilter('rating', '{{ $rating }}')`.

```js
// home-aside.blade.php — value is a PHP-rendered string like '5', '4', etc.
:checked="filters.rating.includes('{{ $rating }}')"
@change="toggleFilter('rating', '{{ $rating }}')"
```

`getFilterUrl()` uses `Math.min(...this.filters.rating)` which coerces strings to numbers implicitly. This works, but the **`init()`** function parses the URL back and stores them as **strings** from `split(',')`. If values were ever stored as integers, the `:checked` binding using `.includes('5')` would silently break.

### Fix
Keep values consistently as **strings** (already the case) and document the contract — or coerce to integers in `toggleFilter` for the rating type only. The real fix is in Bug 4 (init parsing).

---

## Bug 2 — Broken SQL Subquery in Rating Filter ⚠️ Critical

### Location
`HomeController.php` L98–100

### Problem
```php
return $query->whereHas('ratings', function ($q) use ($minRating) {
    $q->selectRaw('avg(rating)')->havingRaw('avg(rating) >= ?', [$minRating]);
});
```

`whereHas()` generates a correlated **EXISTS subquery**. The `havingRaw` inside it operates **without a `GROUP BY`**, so `avg(rating)` aggregates across the entire `ratings` table — not per-post. This means:

- The subquery becomes: `SELECT avg(rating) FROM ratings HAVING avg(rating) >= ?`
- It computes the **global average** for all posts, not the average for the specific post being filtered.
- Result: either **all posts pass** (if global avg ≥ minRating) or **none pass**, regardless of individual post ratings.

### Fix

```php
// Option A — Use the pre-computed withAvg column (recommended)
->when($minRating > 0, fn ($query) => $query->having('average_rating', '>=', $minRating))

// Option B — Proper grouped subquery
->whereHas('ratings', function ($q) use ($minRating) {
    $q->select('post_id')
      ->groupBy('post_id')
      ->havingRaw('avg(rating) >= ?', [$minRating]);
})
```

> This is the most impactful bug — the rating filter is effectively non-functional at the database level.

---

## Bug 3 — Multi-Select Rating Logic Is Counterintuitive

### Location
`home.blade.php` L250–252 and `HomeController.php` L96

### Problem
The frontend allows selecting **multiple rating values** (e.g., `[3, 4, 5]`) and sends the **minimum** to the server:

```js
// getFilterUrl() — only the min is sent
url.searchParams.set('rating', Math.min(...this.filters.rating));
```

The server then shows all posts with rating ≥ that minimum. But the UI shows checkboxes as if they are independent filters. This is misleading because:

- Selecting `3+` and `4+` is equivalent to just selecting `3+`.
- The "selected filters" display shows both `3+ Rating` and `4+ Rating` as separate chips, but only `3+` has any effect.

### Fix
Change the rating UI to **radio buttons** (single-select) instead of checkboxes, or make `toggleFilter` replace instead of append for rating:

```js
// In toggleFilter, treat 'rating' as single-select:
if (type === 'rating') {
    this.filters.rating = this.filters.rating.includes(value) ? [] : [value];
} else {
    // existing multi-select logic
}
```

---

## Bug 4 — `init()` Stores Rating as String, URL Sends Integer

### Location
`home.blade.php` L303–305

### Problem
When restoring state from the URL on page load, `init()` does:

```js
if (params.get('rating')) {
    this.filters.rating = params.get('rating').split(',');
}
```

But `getFilterUrl()` sends a **single integer** (the min), not a comma-separated list:

```js
url.searchParams.set('rating', Math.min(...this.filters.rating)); // sends e.g. "3"
```

- For a single value, `"3".split(',')` → `["3"]` — accidentally works.
- But if the URL contains `?rating=3,4` (manually crafted), the server only uses the minimum while the UI shows multiple chips — a mismatch.

The real issue is that `init()` reads `rating` as a comma-separated list, but `getFilterUrl()` always writes only the minimum. These two are **out of sync in their contract**.

### Fix
Align them — since the server only ever uses the minimum, only store/send one value:

```js
// init — read as a single value
if (params.get('rating')) {
    this.filters.rating = [params.get('rating')]; // single value, not split
}
```

---

## Bug 5 — "All" Rating Checkbox Calls `clearFilters()` — Resets All Filters

### Location
`home-aside.blade.php` L106 and L218

### Problem
```html
<label class="rating-check">
    <input type="checkbox" value="all" :checked="filters.rating.length === 0" @change="clearFilters()">
    <span>All</span>
</label>
```

When the user clicks "All" in the **Rating** section, it calls `clearFilters()` which **resets all filters** (categories, tags, search, sort) — not just the rating filter.

### Fix
```html
<input type="checkbox" value="all"
       :checked="filters.rating.length === 0"
       @change="filters.rating = []; updateResults()">
```

---

## Summary Table

| # | Bug | Severity | File(s) |
|---|-----|----------|---------|
| 1 | String/int type inconsistency in `filters.rating` | Low | home-aside.blade.php, home.blade.php |
| 2 | **Broken SQL subquery** — `whereHas` + `havingRaw` without GROUP BY | **Critical** | HomeController.php |
| 3 | Multi-select rating is misleading; only `Math.min()` is used | Medium | home.blade.php, home-aside.blade.php |
| 4 | `init()` and `getFilterUrl()` out of sync on rating URL format | Low | home.blade.php |
| 5 | "All" rating checkbox calls `clearFilters()` — resets all filters | **High** | home-aside.blade.php |
