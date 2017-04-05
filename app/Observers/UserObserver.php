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
    /**
     * On user model deleted event
     * 
     * @param \App\Models\User $user User model object
     */
    public function deleted(User $user)
    {
        if ($user->isForceDeleting()) {
            $user->picture()->forceDelete();

            return;
        }

        $user->picture()->delete();
    }

    /**
     * On user model restored event
     * 
     * @param \App\Models\User $user User model object
     */
    public function restored(User $user)
    {
        $user->picture()->restore();
    }
}
