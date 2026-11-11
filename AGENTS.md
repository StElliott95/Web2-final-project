# Coding Agent Instructions

## Project and scope

This is a university WEB2 project for a Modern Movie Discovery & Social Platform. Use PHP with Laravel, MySQL, and the MVC architecture required by the course. Keep the work at course-project scope; do not add unrelated product features or architecture.

The current team assignment for this contributor covers project objectives 1, 2, 3, and 7: user authentication, the movie database, TMDB integration, and the integrations listed in the project brief. Do not implement objectives 4, 5, 6, 8, 9, 10, or 11 unless the team leader assigns them. Coordinate shared interface points with the teammate who owns those objectives.

## Read before editing

- Read the project brief and [docs/Frontend_Design_Guidelines.docx](docs/Frontend_Design_Guidelines.docx) for frontend scope, layout, APIs, and design tokens.
- Read [database/README.md](database/README.md), migrations, and Eloquent models before changing data-dependent screens.
- Follow [CONTRIBUTING.md](CONTRIBUTING.md) for branches, commit messages, and pull requests.

## Frontend and MVC

- Use Laravel Blade views and reusable Blade components or partials for the shared layout, navigation, movie cards, and integration panels.
- Keep request handling in controllers and external API communication in Laravel service classes. Views render prepared data; they must not contain provider credentials or call external services directly.
- Keep browser JavaScript limited to needed interactions such as search filters, map interaction, and the real-time discussion connection.
- Follow the agreed restrained dark cinema design: charcoal surfaces, light text, muted gold accents, consistent 8 px spacing, and responsive poster cards.
- Provide loading, empty, and error states for data-backed views. Make forms keyboard-accessible and label all controls.
- Request geolocation only when the user asks to find nearby places. Handle denied permission and unavailable location clearly.

## External services

Use only the services named in the project brief for this scope: TMDB, Weather API, Maps/Geolocation API, Sentiment Analysis API, Calendar Integration, and WebSockets or another real-time technology for discussions. Do not replace them with unrelated services or choose a vendor where the brief does not specify one without asking the team leader.

Sentiment output belongs beside the teammate-owned review feature; do not duplicate review management. The discussion room is part of objective 7.

## Documentation and version control

- Record project decisions and implementation details in Word documents under `docs/`.
- Work on one task branch at a time. Do not commit or push directly to `main`.
- Use Conventional Commit messages and submit a pull request for the team leader's review.
- Do not merge a pull request. Follow the review and merge rules in `CONTRIBUTING.md`.
