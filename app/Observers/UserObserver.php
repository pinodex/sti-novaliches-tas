<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function deleted(User $user)
    {
        if ($user->isForceDeleting()) {
            $user->picture()->forceDelete();

            return;
        }

        $user->picture()->delete();
    }

    public function restored(User $user)
    {
        $user->picture()->restore();
    }
}
