VISUAL QA STANDARD

This project requires Visual QA before every commit.

====================================================

After EVERY feature implementation:

Run

php artisan optimize:clear

php artisan test

npm run build

====================================================

Launch browser.

Do NOT rely only on HTTP status codes.

Actually verify the UI.

====================================================

Capture screenshots.

Required

Login

Dashboard

Ticket List

Create Ticket

Ticket Detail

Profile

Settings

====================================================

Review every screenshot.

Look for

Broken layout

Missing CSS

Empty dropdown

Broken icons

Overflow

Wrong alignment

404

500

Validation issue

Console errors

====================================================

If ANY issue exists

STOP

Explain

Root Cause

Files

Fix

Apply Fix

Run Visual QA again.

Repeat until screenshots are clean.

====================================================

Only then

Git Commit

Git Push