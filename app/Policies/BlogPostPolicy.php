<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->allows('blog.view');
    }

    public function view(User $user, BlogPost $blogPost): bool
    {
        return $user->allows('blog.view');
    }

    public function create(User $user): bool
    {
        return $user->allows('blog.create');
    }

    public function update(User $user, BlogPost $blogPost): bool
    {
        return $user->allows('blog.update');
    }

    public function delete(User $user, BlogPost $blogPost): bool
    {
        return $user->allows('blog.delete');
    }
}
