<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CalendarExportController extends Controller
{
    public function __invoke(Movie $movie): Response
    {
        abort_unless($movie->release_date !== null, 404, 'This movie has no release date.');

        $start = $movie->release_date->format('Ymd');
        $end = $movie->release_date->copy()->addDay()->format('Ymd');
        $description = $this->escape($movie->overview ?? '');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//WEB2 Movie Discovery//Calendar Integration//EN',
            'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:movie-' . ($movie->tmdb_id ?: $movie->id) . '@web2-project.local',
            'DTSTAMP:' . now('UTC')->format('Ymd\\THis\\Z'),
            'DTSTART;VALUE=DATE:' . $start,
            'DTEND;VALUE=DATE:' . $end,
            'SUMMARY:' . $this->escape($movie->title),
            'DESCRIPTION:' . $description,
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        $filename = (Str::slug($movie->title) ?: 'movie') . '-release.ics';

        return response(implode("\r\n", $lines) . "\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function escape(string $value): string
    {
        $value = str_replace(["\\", ";", ","], ["\\\\", "\\;", "\\,"], $value);
        return str_replace(["\r\n", "\r", "\n"], "\\n", $value);
    }
}
