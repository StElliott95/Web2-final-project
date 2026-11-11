<?php

namespace App\Http\Controllers;

use App\Models\DiscussionMessage;
use App\Models\DiscussionRoom;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DiscussionRoomController extends Controller
{
    public function show(Movie $movie): View
    {
        $room = DiscussionRoom::query()
            ->where('movie_id', $movie->id)
            ->where('is_active', true)
            ->first();

        if (! $room) {
            $room = DiscussionRoom::create([
                'movie_id' => $movie->id,
                'created_by' => auth()->id(),
                'title' => mb_substr($movie->title . ' discussion', 0, 150),
                'is_active' => true,
            ]);
        }

        $this->joinRoom($room->id, (int) auth()->id());

        $messages = $room->messages()
            ->with('sender:id,name')
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->sortBy('id')
            ->values();

        return view('Discussion.room', compact('movie', 'room', 'messages'));
    }

    public function send(Request $request, DiscussionRoom $room): JsonResponse
    {
        $request->merge(['message' => trim((string) $request->input('message'))]);
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $this->ensureMember($room);

        $message = $room->messages()->create([
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ])->load('sender:id,name');

        return response()->json([
            'id' => $message->id,
            'sender' => $message->sender->name,
            'message' => $message->message,
            'sent_at' => $message->created_at?->toIso8601String(),
        ], 201);
    }

    public function stream(Request $request, DiscussionRoom $room): StreamedResponse
    {
        $this->ensureMember($room);
        $lastId = max(0, (int) $request->header('Last-Event-ID', $request->query('after', 0)));

        return response()->stream(function () use ($room, $lastId): void {
            $cursor = $lastId;
            $deadline = microtime(true) + 20;

            echo "retry: 2000\n\n";
            if (ob_get_level() > 0) {
                @ob_flush();
            }
            flush();

            while (! connection_aborted() && microtime(true) < $deadline) {
                $messages = DiscussionMessage::query()
                    ->with('sender:id,name')
                    ->where('room_id', $room->id)
                    ->where('id', '>', $cursor)
                    ->orderBy('id')
                    ->limit(50)
                    ->get();

                if ($messages->isEmpty()) {
                    echo ": keep-alive\n\n";
                } else {
                    foreach ($messages as $message) {
                        $payload = json_encode([
                            'id' => $message->id,
                            'sender' => $message->sender->name,
                            'message' => $message->message,
                            'sent_at' => $message->created_at?->toIso8601String(),
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                        echo 'id: ' . $message->id . "\n";
                        echo "event: message\n";
                        echo 'data: ' . $payload . "\n\n";
                        $cursor = $message->id;
                    }
                }

                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                flush();
                usleep(800000);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function joinRoom(int $roomId, int $userId): void
    {
        $now = now();

        DB::table('discussion_room_members')->updateOrInsert(
            ['room_id' => $roomId, 'user_id' => $userId],
            ['joined_at' => $now, 'left_at' => null, 'created_at' => $now, 'updated_at' => $now]
        );
    }

    private function ensureMember(DiscussionRoom $room): void
    {
        abort_unless($room->is_active, 404);

        $isMember = DB::table('discussion_room_members')
            ->where('room_id', $room->id)
            ->where('user_id', auth()->id())
            ->whereNull('left_at')
            ->exists();

        abort_unless($isMember, 403, 'Join this discussion room before accessing it.');
    }
}
