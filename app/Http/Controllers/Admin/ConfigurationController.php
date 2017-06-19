<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Components\Acl;

class ConfigurationController extends Controller
{
    public function __construct()
    {
        $this->can(Acl::ADMIN_CONFIGURATION);
    }

    /**
     * Configuration index page
     * 
     * @return mixed
     */
    public function index()
    {
        return view('admin.configuration.index');
    }
}
