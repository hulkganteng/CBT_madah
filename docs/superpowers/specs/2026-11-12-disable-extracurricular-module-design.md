# Disable Extracurricular Module

## Intent

Keep this deployment focused on CBT by removing the Extracurricular navigation entry and denying direct access to the legacy module.

## Approach

Reuse the existing retired-module guard. Add `dataekstra` to its controller denylist and remove the active sidebar entry. Preserve the controller, database tables, and historical data.

## Constraints

- Return HTTP 403 with the existing “Modul Dinonaktifkan” response.
- Do not delete data or legacy files.
- Add source-level regression assertions for both navigation and direct-access guards.
