# Database task

This folder contains the MySQL database layer for the assigned WEB2 objectives: user authentication and profiles, the TMDB movie catalogue, and the listed API integrations.

## Requirements

- PHP with a Laravel application
- MySQL with InnoDB
- A configured Laravel .env file using DB_CONNECTION=mysql

## Apply the schema

Run the migrations from the Laravel project root:

    php artisan migrate

The repository was empty when these migrations were added. If a fresh Laravel project already has its default create_users_table migration, merge the user fields from this schema into that migration or remove the duplicate before migrating.

## Scope and integration notes

- Movie data is keyed by TMDB identifiers and cached in MySQL for catalogue pages and search.
- Weather API and Maps/Geolocation API responses are request-time data and are not stored by this schema.
- Calendar credentials are stored in calendar connection rows and encrypted by Laravel model casts.
- Discussion room messages are persisted in MySQL; WebSockets handle real-time delivery.
- Sentiment Analysis API results belong with review records managed by the teammate assigned to reviews. Agree on the review table and result fields before integration; this schema does not create or duplicate that teammate-owned table.
- API keys belong in Laravel environment configuration, not in database rows. The brief names the integration services but does not specify endpoint URLs or API versions, so this database layer does not invent them.

## Tables

users, social_accounts, password_reset_tokens, sessions, movies, genres, movie_genre, people, movie_credits, calendar_connections, calendar_events, discussion_rooms, discussion_room_members, discussion_messages.
