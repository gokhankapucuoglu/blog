<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class UserPolicy
{
    use HandlesAuthorization;

    private function isProtected(AuthUser $authUser, User $model): bool
    {
        // 1. Kendi kendine işlem yapamaz (Silme, Kalıcı Silme vs.)
        if ($authUser->id === $model->id) return true;

        // 2. Sistem hesabı (ID:1) her zaman korunur
        if ($model->id === 1) return true;

        // 3. Düz Admin, Super Admin'e dokunamaz
        if ($model->hasRole('super_admin') && ! $authUser->hasRole('super_admin')) return true;

        return false;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:User');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:User');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:User');
    }

    public function update(AuthUser $authUser, User $model): bool
    {
        // Düz Admin, Super Admin'i URL'den girmeye çalışsa bile engel ol
        if ($model->hasRole('super_admin') && ! $authUser->hasRole('super_admin')) {
            return false;
        }

        return $authUser->can('Update:User');
    }

    public function delete(AuthUser $authUser, User $model): bool
    {
        if ($this->isProtected($authUser, $model)) return false;

        return $authUser->can('Delete:User');
    }

    public function restore(AuthUser $authUser, User $model): bool
    {
        if ($this->isProtected($authUser, $model)) return false;

        return $authUser->can('Restore:User');
    }

    public function forceDelete(AuthUser $authUser, User $model): bool
    {
        if ($this->isProtected($authUser, $model)) return false;

        return $authUser->can('ForceDelete:User');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:User');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:User');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:User');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:User');
    }
}
