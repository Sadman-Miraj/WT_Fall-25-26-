# Copilot instructions — WT_Fall-25-26

**Purpose:** Short, actionable guidance so an AI coding agent can be productive immediately in this simple PHP lab/project repository.

## Big picture 🔧
- This repo is a collection of small **PHP/HTML** lab pages (top-level files like `index.php`, `validation.php`, `onclick.php`) plus a small web app under `project/` (Automobiles Solution).
- No framework or dependency manager is used (no Composer). Expect plain PHP files, inline JS/CSS, and static assets in `project/css/` and `project/image/`.
- Typical flow: client → static PHP page → (optional) PHP handler → MySQL (via XAMPP). Many pages are client-only demos; some links point to missing server endpoints.

## How to run locally ▶️
- Primary: Windows + XAMPP: place repo under `htdocs`, start Apache (+MySQL if needed), then browse to `http://localhost/<repo-folder>/`.
- Alternative (quick dev server): from a folder with PHP files run `php -S localhost:8000` and open `http://localhost:8000` (note: relative links assume the current folder layout).
- Quick lint: `php -l path/to/file.php`.

## Key files to inspect 📂
- `index.php` (top-level demo index)
- `validation.php` (client-side JS validation example)
- `onclick.php`, `on.php` (UI/JS demos)
- `project/php/index.php` (Automobiles Solution homepage — links to `../css/index.css`, references `login.php`/`signup.php` which are missing)
- `project/db/db.php` (intended DB connector — currently contains invalid placeholder text `hhgit a` and must be repaired before DB work)

## Project-specific patterns & gotchas ⚠️
- Heavy use of relative paths (e.g., `../css/index.css`). When moving files, update links accordingly.
- UI logic is often inline in HTML; form validation is client-side (`validation.php`). If adding server-side logic, add corresponding PHP handlers (forms often POST to `/submit` or `action_page.php` that do not exist).
- No tests, no CI configuration. Keep changes small and verify in browser.
- DB safety: do not embed credentials in code committed to repo. Use `project/db/db.php` as a single DB wrapper and prefer PDO + prepared statements.

## Concrete examples & immediate tasks ✅
- Repair `project/db/db.php` to a PDO connection template and add a short note about local credentials (e.g., `localhost`, `root`, empty password for XAMPP by default).
- Add missing page stubs referenced by `project/php/index.php` (`login.php`, `signup.php`) and ensure header links work.
- Replace critical inline JS/CSS with asset files in `project/js/` or `project/css/` and update HTML references if refactoring UX.
- Add a short `README.md` at repo root documenting how to start XAMPP, example URLs, and where DB config lives.

## Rules for AI agents (keep it minimal & precise) 🤖
- Before editing DB code: inspect `project/db/db.php` (it currently contains `hhgit a`) and confirm a working DB wrapper exists.
- When adding endpoints referenced by forms, ensure the form `action` matches an actual file and method (POST/GET) and include a short manual test in the PR description.
- Preserve existing relative path patterns; prefer updating links over moving many files.
- Run `php -l` on modified files and confirm pages load in a local browser (XAMPP or `php -S`).

---

If you'd like, I can: repair `project/db/db.php` to a PDO template, add `login.php`/`signup.php` stubs under `project/php/`, and create a short README with run instructions. Which should I do first?