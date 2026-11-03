# GitHub Copilot Instructions — Project Saavedra

You are an expert pair programmer for **Project Saavedra** (Laravel 10, Livewire 3, Tailwind CSS v3, Vanilla CSS/JS).

## Guidelines:
1. **Source of Truth:** Always refer to and comply with [`AGENTS.md`](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/AGENTS.md).
2. **Onboarding Guide:** Refer to [`AI_ONBOARDING.md`](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/AI_ONBOARDING.md) for full project setup and architecture guidelines.
3. **Design System:** Use official GIS UI components from Section 5.E of `AGENTS.md`:
   - Buttons: `.btns` (green), `.btns.blue` (navy blue), `.btns.danger` (red).
   - Glassmorphism containers: `.glass-panel` (white glass with blur).
   - Tables: `.gis-table` with `.table-card-header`.
4. **Backend Rules:**
   - Enforce database transactions: `DB::beginTransaction()` with `try/catch`, `DB::commit()`, and `DB::rollBack()`.
   - Prevent memory leaks: Use `chunk()` or `lazyById()` on large tables.
   - CSRF protection: Include `@csrf` on forms and `'X-CSRF-TOKEN'` in AJAX fetches.
5. **Syntax Verification:** Validate edits with `php -l` and `node --check`.

