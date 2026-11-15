# WEB2 Final Project

Course deliverables for the Modern Movie Discovery & Social Platform project.

## Stack

- PHP with Laravel using MVC
- MySQL database

## Database task

This repository contains the database layer for the assigned objectives: user authentication, the movie catalogue with TMDB data, and the API integrations named in the project brief. See [database/README.md](database/README.md) for migration setup and integration boundaries. The Word report is in [docs/Database_Design_Task_2.docx](docs/Database_Design_Task_2.docx).

## Frontend guidelines

- [Frontend Design Guidelines](docs/Frontend_Design_Guidelines.docx) defines the human-readable layout, design, MVC, and integration guidance.
- [AGENTS.md](AGENTS.md) gives coding agents repository-specific instructions.
- [CONTRIBUTING.md](CONTRIBUTING.md) defines the branch, commit, and pull request workflow.

## Team workflow

Use the branch and pull request process in CONTRIBUTING.md. The main-branch protection settings are documented in .github/MAIN_BRANCH_RULES.md.

## Task 7 integrations

- [Task 7 API Integrations](docs/Task_7_API_Integrations.docx) documents the weather, maps/geolocation, sentiment, calendar, and real-time discussion integrations.
- Weather uses Open-Meteo and nearby cinemas use OpenStreetMap data through Overpass as no-key defaults; the page includes provider attribution. Open-Meteo's free API is for non-commercial use. Sentiment analysis defaults to Hugging Face Inference with an open CardiffNLP English model; set `SENTIMENT_API_TOKEN` in `.env` to enable it. Hugging Face's free monthly inference credits are limited and may change.

## Running the integrated tasks

Requires PHP 8.2 or newer, Composer, and MySQL. Blade templates use the shared inline styles; no Node build is required.

1. Run `composer install` and copy `.env.example` to `.env`.
2. Create an empty MySQL database and configure `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`.
3. Run `php artisan key:generate` and `php artisan migrate`.
4. For local demonstration data, run `php artisan db:seed --class=TeamDemoSeeder`.
5. Run `php artisan serve`. With XAMPP in `C:\xampp\htdocs\Web2`, use `http://localhost/Web2/public` instead.

The seeder creates local demonstration accounts and runs only in local/testing environments. Do not use its accounts outside local development. Users can register, manage their profile, change passwords, and request password-reset links.

## Authentication

Registration and sign-in use the Laravel session guard. Passwords are hashed by the `User` model, login and recovery endpoints are rate-limited, and password resets use Laravel's password broker and the existing `password_reset_tokens` table. Profile updates never accept a role or account-status field; password-authenticated sessions require the current password for email changes, while GitHub-authenticated sessions use the active GitHub sign-in.

The example environment uses `MAIL_MAILER=log`, so password-reset links are written to `storage/logs/laravel.log` during local development. Configure Laravel's `MAIL_*` settings for a real mail transport before using password recovery outside a local demo.

GitHub sign-in is shown after `GITHUB_OAUTH_CLIENT_ID` and `GITHUB_OAUTH_CLIENT_SECRET` are configured. Create a GitHub OAuth App and set its Authorization callback URL to the app URL followed by `/auth/github/callback` (for the XAMPP example: `http://localhost/Web2/public/auth/github/callback`). Set `GITHUB_OAUTH_REDIRECT_URI` to that same URL in `.env`. The app requests `read:user` and `user:email`, requires a verified email address, and does not store GitHub access tokens. Existing local accounts with the same verified email are linked to the GitHub identity.

Member flow: sign in, open **For you**, edit genre/actor/director preferences, then manage **My lists** or browse **Community**. Private lists are visible only to their owner. Public lists remain editable only by their owner.

Admin flow: sign in, open **Admin**, then manage users, edit local movies, use the existing review moderation screen, and inspect statistics and activity. Suspended users cannot sign in or retain a session. Administrators cannot suspend, demote, or delete themselves.

Task 6 uses normalized catalogue relationships, viewing history, and the team's 1–10 review scale. Task 8 adds private/public lists with unique movie membership. Task 11 reuses existing review moderation and adds administration and activity tracking. All features share the team's charcoal and muted-gold Blade layout.

Set `TMDB_READ_ACCESS_TOKEN` for live discovery. Without it, discovery and details use saved catalogue data. Set `SENTIMENT_API_TOKEN` to a Hugging Face user token with Inference Providers permission to enable review sentiment. Keep both tokens in `.env`; never commit them. The local demo contains six saved films with unavailable-poster placeholders.

## Integration tests

The authentication review suite is `tests/Feature/AuthenticationFlowTest.php`. It covers registration, privileged-field protection, remember-me login, suspension, profile and password changes, password reset notification/token handling, and GitHub callback validation. Run `php artisan test --filter=AuthenticationFlowTest` with the isolated test database configured below. OAuth HTTP responses and reset notifications are faked; these checks do not verify real GitHub credentials or email delivery.

Create a separate empty MySQL database with `_test_` in its name. Copy `.env` to `.env.testing`, set `APP_ENV=testing` and point its database credentials to this isolated database. Never use the demonstration or shared team database for tests. Run `php artisan test`; the suite migrates the isolated database and checks recommendations, list ownership, admin permissions, suspension, normalized movie edits, moderation, and unique viewer statistics.

The merge sequence was PR #7 runtime foundation → PR #6 API integrations → PR #8 recommendations → PR #9 movie lists → PR #10 administration. Each dependent branch was synchronized after its prerequisite merged.

## Final team integration

Implementation notes are in [Task 6](docs/Task_6_Integration.docx), [Task 8](docs/Task_8_Integration.docx), and [Task 11](docs/Task_11_Integration.docx). All three were visually checked in Microsoft Word at 100% zoom. The combined notes and tests are on the final task-11 branch.

The team leader merged PRs #7 → #6 → #8 → #9 → #10 in dependency order. After each prerequisite merges, update and retarget the next PR to `main` and inspect its diff before merging. If squash merging changes the prerequisite history, remove already-merged changes from the next PR through the agreed team workflow; do not force-push shared branches without agreement.

Authentication uses Laravel's session guard. Registration creates ordinary active users; role and account-status fields are not accepted from forms. Login rejects inactive accounts, and the shared middleware ends sessions for accounts suspended by an administrator. Review/watch-history owners should retain the shared internal movie IDs and the 1-10 rating scale. Movie detail URLs continue to use TMDB IDs.

The TMDB owner must supply `TMDB_READ_ACCESS_TOKEN` privately in their local environment, clear cached configuration, and verify live discovery, details and posters. Do not commit credentials. This integration was verified using the saved catalogue because no live token was available.

On a fresh checkout, follow the setup above and run `php artisan migrate` against a dedicated project database. Use the demo seeder only for local demonstrations. Create an isolated `_test_` MySQL database in `.env.testing`, then run `php artisan test`. Finally, exercise member login, preferences, recommendations, private/public lists, admin access, review moderation and suspension together with the other teammates' completed features. Each teammate remains responsible for their assigned objectives before the final course demonstration.
