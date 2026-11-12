# Disable Extracurricular Module Plan

## Context

The application is scoped to CBT, but the active admin sidebar still exposes Extracurricular data and `/dataekstra` remains directly accessible.

## Approach

1. Remove the active sidebar entry.
2. Add `dataekstra` to `Exam_security_hook::denyRetiredModules()`.
3. Assert that the active sidebar has no route and the retired-module guard contains the controller.

## Out of scope

- Deleting the controller, views, tables, or existing extracurricular records.
- Editing historical `sidebar_old.php` snapshots.

## Verification

- PHP lint the changed PHP files.
- Run source-level assertions for the menu and retired-module guard.
- Run the existing security regression suite and report its known unrelated failure separately.
