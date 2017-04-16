<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\MultiAuth\Providers;

use Illuminate\Contracts\Auth\UserProvider as BaseUserProvider;

interface ProviderInterface extends BaseUserProvider
{
    /**
     * Get model used by the provider
     * 
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function getProvidingModel();
}
