<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

final class BrassShowroomAuthorization
{
    public static function allows(?Authenticatable $user): bool
    {
        if (! $user) {
            return false;
        }

        if (! method_exists($user, 'tokenCan') || ! $user->tokenCan('showroom:manage')) {
            return false;
        }

        $ids = config('brass-showroom.manager_user_ids', []);
        $emails = array_map('strtolower', config('brass-showroom.manager_emails', []));
        $idAllowed = in_array((int) $user->getAuthIdentifier(), $ids, true);
        $email = strtolower((string) ($user->email ?? ''));

        return $idAllowed || ($email !== '' && in_array($email, $emails, true));
    }
}
