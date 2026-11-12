# Exam Frontend Reliability Plan

## Context

The participant exam UI can ignore proctor-added time until the next question load, keep the current answer only in browser memory, and expose matching questions through pointer-only controls. The intended outcome is a server-synchronised timer, durable answer autosave, race-safe question state, and keyboard/touch-accessible controls without changing the database or grading rules.

## Approach

1. Make the heartbeat response (`sisa_detik`) the authoritative timer signal. Use a wall-clock deadline so browser timer throttling does not accumulate drift.
2. Add one serialised, debounced answer-save queue. Navigation waits for the latest revision; failed background saves remain dirty and retry without discarding the visible answer.
3. Lock navigation while the doubt marker is being saved and bind its callback to the originating question.
4. Upgrade matching items to keyboard-operable controls and expose navigator state through labels in addition to colour.
5. Increase touch targets and declare the document language.

## Key decisions

- Reuse the existing `savejawaban`, heartbeat, and jQuery request paths; no dependency or schema changes.
- Autosave to the server rather than local storage because exam answers already have an authenticated persistence endpoint.
- Preserve the existing rule that all questions must be answered before manual submission.
- Keep server validation authoritative; client state only improves durability and feedback.

## Files to modify

- `application/views/members/siswa/cbt/ujian.php`: wall-clock timer, autosave queue, doubt-marker sequencing, navigator semantics, touch sizing.
- `assets/app/js/exam-guard.js`: continue publishing complete heartbeat payloads; no protocol change required.
- `assets/app/js/linker-list.js`: matching-question keyboard semantics.
- `application/views/members/siswa/templates/header.php`: Indonesian document language.
- `application/views/members/siswa/cbt/konfirmasi.php`: prevent repeated start submissions.

## Out of scope

- Database migrations, scoring logic, SEB policy, session takeover rules, and retired E-Learning/Rapor modules.
- Replacing jQuery/AdminLTE or redesigning the exam screen.

## Verification

- `php -l application/views/members/siswa/cbt/ujian.php` — no syntax errors.
- `php -l application/views/members/siswa/cbt/konfirmasi.php` — no syntax errors.
- `node --check assets/app/js/exam-guard.js` — no syntax errors.
- `node --check assets/app/js/linker-list.js` — no syntax errors.
- Static checks confirm heartbeat remaining time is consumed, matching controls have keyboard handling, and navigator targets are at least 44px.
- Run `php tools/test-security-regressions.php`; report any pre-existing unrelated failure separately.

## STOP conditions

- Stop if `savejawaban` cannot safely accept repeated idempotent updates for the same mapped question.
- Stop if the heartbeat payload no longer includes integer `sisa_detik`.
