# SEO Fix Plan — livescorestips.com

## Priority 1: Content Structure & Source Order

### 1.1 Move hidden modals to bottom of body
**Files:** `resources/views/layouts/app.blade.php`, `resources/views/components/layout/navbar.blade.php`
**Issue:** Login/register/settings/filter modals are rendered in `<head>` order before page content.
**Fix:** Move all modal HTML (Settings, Filter, Login, Register) from `navbar.blade.php` to the bottom of `app.blade.php`, after `<x-layout.footer />` and before closing `</body>`. These modals have `style="display:none"` so position in DOM doesn't affect UX, only crawl order.

### 1.2 Add a proper `<h1>` tag
**Files:** `resources/views/layouts/app.blade.php`, `resources/views/home.blade.php`
**Issue:** Page uses `<h2 class="bm-page-title">` instead of `<h1>`. No H1 exists anywhere.
**Fix:** 
- In `app.blade.php`, change `@yield('page-header')` container to use `<h1>` when it's the page header, or better: update the `home.blade.php` and other views to use `<h1>` for the page title.
- Also change login/register modal headings from `<h2>` to `<h3>` or `<h4>` to avoid having UI modal headings compete with content headings.

### 1.3 Add strong SEO intro text under H1
**Files:** `resources/views/home.blade.php`, database `metadata` table
**Issue:** Valuable SEO content (the info article / footer_content) appears last on the page.
**Fix:** Add a 150-250 word introductory paragraph between the page-header and the filter toolbar. This should be editable via the metadata system (new `header_intro` key) and contain primary keywords. The existing `info-article` deeper content stays for depth.

### 1.4 Reduce filter UI crawl footprint
**Files:** `resources/views/components/filter-toolbar.blade.php`
**Issue:** Dropdown items (`<li>` elements with filter text) are rendered in HTML and crawled.
**Fix:** Keep the button labels but move the dropdown `<ul>` contents to be rendered dynamically via JavaScript instead of server-side HTML. This reduces ~50 lines of filter options from the crawlable source.

---

## Priority 2: Technical SEO

### 2.1 Add canonical tag
**File:** `resources/views/layouts/app.blade.php`
**Issue:** No `<link rel="canonical">` exists.
**Fix:** Add `<link rel="canonical" href="{{ url()->current() }}">` in `<head>`. For date-based URLs like `/home/2026-07-07`, if the date is today, canonical should point to `/`.

### 2.2 Add robots meta tag
**File:** `resources/views/layouts/app.blade.php`
**Issue:** No explicit `<meta name="robots">`.
**Fix:** Add `<meta name="robots" content="index, follow">` by default. Allow individual pages to override via a `@section('robots')`.

### 2.3 Add Twitter Card tags
**File:** `resources/views/layouts/app.blade.php`
**Issue:** Missing `twitter:card`, `twitter:title`, `twitter:description`, `twitter:image`.
**Fix:** Add alongside existing OG tags:
```html
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('title')">
<meta name="twitter:description" content="@yield('description')">
<meta name="twitter:image" content="{{ asset($ogImage ?? 'images/icon.png') }}">
```

### 2.4 Add `og:locale`
**File:** `resources/views/layouts/app.blade.php`
**Fix:** Add `<meta property="og:locale" content="en_US">`

### 2.5 Add resource hints (preconnect)
**File:** `resources/views/layouts/app.blade.php`
**Fix:** Add `<link rel="preconnect">` for Google Fonts, Flatpickr CDN, and API services:
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net">
```

### 2.6 Expand JSON-LD structured data
**File:** `resources/views/layouts/app.blade.php`
**Fix:** Add `Organization` schema (with logo, URL, social profiles), `BreadcrumbList` schema (for inner pages), and `SportsEvent` schema on prediction/fixture pages.

---

## Priority 3: Content & Indexing

### 3.1 Fix empty country pages
**File:** `resources/views/country/show.blade.php`
**Issue:** Pages with "No upcoming predictions found" are indexed with minimal content.
**Fix:** 
- Option A: Add `@if($predictions->isEmpty()) @push('head') <meta name="robots" content="noindex"> @endpush @endif`
- Option B: Add evergreen content about country's football leagues, common betting markets, and prediction patterns for that country.

### 3.2 Limit country sidebar links or noindex thin pages
**File:** `resources/views/components/layout/sidebar.blade.php`
**Issue:** Full country list links to potentially many thin pages.
**Fix:** Limit sidebar to top 30 countries by match volume. Move full list to a dedicated `/countries` page or keep the JS search but cap visible links.

### 3.3 Standardize brand name
**Files:** `.env`, `resources/views/home.blade.php`, `resources/views/layouts/app.blade.php`, `config/app.php`
**Issue:** "LiveScores Tips", "LiveScore tips", "LiveScoresTips", "LiveScoreTips" used inconsistently.
**Fix:** Pick one — recommend **LiveScoresTips** (single capitalized word, matches domain). Update:
- `.env`: `APP_NAME="LiveScoresTips"`
- All Blade `config('app.name', ...)` fallbacks to "LiveScoresTips"
- Title tag to remove double space

### 3.4 Fix social links with `#`
**File:** `resources/views/components/layout/footer.blade.php`
**Fix:** Replace Facebook/Instagram/Discord `href="#"` with actual profile URLs or remove the icons if no profiles exist yet.

### 3.5 Fix title tag double space
**File:** `resources/views/home.blade.php` (line 3)
**Issue:** Title has "Tips  |" with double space.
**Fix:** Clean up the title tag to remove extra whitespace. Ensure the format is consistent.

---

## Implementation Order

| Step | Task | Effort | Impact |
|------|------|--------|--------|
| 1 | Fix H1/H2 heading structure | Low | High |
| 2 | Move hidden modals to bottom | Medium | High |
| 3 | Add canonical + robots meta | Low | High |
| 4 | Add SEO intro text | Low | Medium |
| 5 | Standardize brand name | Low | Medium |
| 6 | Add Twitter Cards + og:locale | Low | Medium |
| 7 | Fix filter crawl footprint | Medium | Medium |
| 8 | Add resource hints | Low | Medium |
| 9 | Fix empty country pages | Low | Medium |
| 10 | Expand JSON-LD | Medium | Medium |
| 11 | Fix social links | Low | Low |
| 12 | Limit country sidebar links | Low | Low |
