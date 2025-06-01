<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Content;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_content');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Content $content): bool
    {
        // Anyone with view permission can view content
        if ($user->can('view_content')) {
            return true;
        }

        // Authors can view their own content
        return $user->id === $content->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_content');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Content $content): bool
    {
        // Editors and admins can update any content
        if ($user->hasRole(['admin', 'editor'])) {
            return true;
        }

        // Authors can only update their own content
        return $user->can('update_content') && $user->id === $content->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Content $content): bool
    {
        // Admins can delete any content
        if ($user->hasRole('admin')) {
            return true;
        }

        // Editors can delete content
        if ($user->hasRole('editor') && $user->can('delete_content')) {
            return true;
        }

        // Authors can only delete their own content
        return $user->can('delete_content') && $user->id === $content->user_id;
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_content');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Content $content): bool
    {
        return $user->can('force_delete_content');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_content');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Content $content): bool
    {
        return $user->can('restore_content');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_content');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Content $content): bool
    {
        return $user->can('replicate_content');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_content');
    }

    /**
     * Determine whether the user can publish content.
     */
    public function publish(User $user, Content $content): bool
    {
        return $user->can('publish_content');
    }

    /**
     * Determine whether the user can review content.
     */
    public function review(User $user, Content $content): bool
    {
        return $user->can('review_content');
    }
}