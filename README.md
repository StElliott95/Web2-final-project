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
- The brief does not name vendors for the Weather, Maps/Geolocation, or Sentiment Analysis APIs. Configure compatible endpoints and credentials in Laravel environment variables; the documented JSON contracts are used by the application.
