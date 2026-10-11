<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->allows('pages.view');
    }

    public function view(User $user, Page $page): bool
    {
        return $user->allows('pages.view');
    }

    public function create(User $user): bool
    {
        return $user->allows('pages.create');
    }

    public function update(User $user, Page $page): bool
    {
        return $user->allows('pages.update');
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->allows('pages.delete');
    }
}
