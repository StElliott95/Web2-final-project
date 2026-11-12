<?php
namespace App\Policies;
use App\Models\{MovieList,User};
class MovieListPolicy {
    public function view(?User $user, MovieList $list): bool { return $list->is_public || $user?->id === $list->user_id; }
    public function update(User $user, MovieList $list): bool { return $user->id === $list->user_id; }
    public function delete(User $user, MovieList $list): bool { return $this->update($user,$list); }
}
