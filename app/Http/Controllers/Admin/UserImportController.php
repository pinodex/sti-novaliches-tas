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

use Auth;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Forms\UserImportUploadForm;
use App\Components\Import\UserImporter;
use App\Components\Import\InvalidSheetException;
use App\Jobs\UserImportJob;
use App\Models\Department;
use App\Models\Profile;
use App\Models\Group;

class UserImportController extends Controller
{
    /**
     * @var string Cache prefix
     */
    protected $cachePrefix = 'import:';

    /**
     * User import index page
     * 
     * @return mixed
     */
    public function index()
    {
        $departments = Department::all();
        $profiles = Profile::all();
        $groups = Group::all();

        return view('admin.users.import.index', [
            'current_step'  => 'index',
            'groups'        => $groups,
            'profiles'      => $profiles,
            'departments'   => $departments
        ]);
    }

    /**
     * User import download action
     * 
     * @return mixed
     */
    public function download()
    {
        return response()->download(resource_path('assets/User_Import_Template.xltx'));
    }

    /**
     * User import upload page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function upload(Request $request)
    {
        $form = with(new UserImportUploadForm)
            ->getForm()->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            
            $filePath = $data['file']->getPathname();
            $defaults = [
                'department_id'    => $data['default_department'],
                'group_id'         => $data['default_group'],
                'profile_id'       => $data['default_profile'],
                'password'         => $data['default_password']
            ];

            try {
                $importer = UserImporter::create($filePath, $defaults);
            } catch (InvalidSheetException $e) {
                return redirect()->route('admin.users.import.index')
                    ->with('message', ['danger', $e->getMessage()]);
            }

            return redirect()->route('admin.users.import.confirm', [
                'id' => $importer->getSessionId()
            ]);
        }

        return view('admin.users.import.upload', [
            'current_step'  => 'upload',
            'form'          => $form->createView()
        ]);
    }

    /**
     * Confirm import page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param string $id Cache key
     * 
     * @return mixed
     */
    public function confirm(Request $request, $id)
    {
        $importer = UserImporter::get($id);

        if (!$importer) {
            return redirect()->route('admin.users.import.index')
                ->with('message', ['danger', 'Cannot find import session']);
        }

        if ($importer->isLocked()) {
            return redirect()->route('admin.users.import.index')
                ->with('message', ['warning', 'Import session already finished']);
        }

        $contents = $importer->getContents();

        if ($request->getMethod() == 'POST') {
            $importer->lock();

            $this->dispatch(new UserImportJob($importer, Auth::user()));

            return redirect()->route('admin.users.import.finish', [
                'id' => $importer->getSessionId()
            ]);
        }

        return view('admin.users.import.confirm', [
            'current_step'  => 'confirm',
            'contents'      => $contents
        ]);
    }

    public function finish(Request $request, $id)
    {
        $importer = UserImporter::get($id);

        if (!$importer) {
            return redirect()->route('admin.users.import.index')
                ->with('message', ['danger', 'Cannot find import session']);
        }

        if (!$importer->isLocked()) {
            return redirect()->route('admin.users.import.confirm', [
                'id' => $importer->getSessionId()
            ])->with('message', ['warning', 'Import session not yet finished']);
        }

        return view('admin.users.import.finish', [
            'current_step'  => 'finish'
        ]);
    }
}
