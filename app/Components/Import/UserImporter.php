<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\Import;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use App\Models\Department;
use App\Models\Profile;
use App\Models\Group;
use App\Models\User;
use Hash;
use DB;

class UserImporter extends Importer
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $departments;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $profiles;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $groups;

    /**
     * @var \App\Models\Department
     */
    protected $defaultDepartment;

    /**
     * @var \App\Models\Profile
     */
    protected $defaultProfile;

    /**
     * @var \App\Models\Group
     */
    protected $defaultGroup;

    /**
     * @var string
     */
    protected $defaultPassword;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var array
     */
    protected $fillable;

    /**
     * @var array Columns required for validity checking
     */
    protected $columns = [
        'First Name',
        'Middle Name',
        'Last Name',
        'Email',
        'Username',
        'Password',
        'Group',
        'Department',
        'Profile'
    ];

    /**
     * @var array Columns to resolve ID from model
     */
    protected $resolveIds = [
        'department_id' => 'department',
        'profile_id'    => 'profile',
        'group_id'      => 'group'
    ];

    public function __construct($sessionId, $filePath, $defaults = [], $duration = 60)
    {
        $this->departments = Department::all();
        $this->profiles = Profile::all();
        $this->groups = Group::all();

        $this->defaultDepartment = $this->departments->find($defaults['department_id']);
        $this->defaultProfile = $this->profiles->find($defaults['profile_id']);
        $this->defaultGroup = $this->groups->find($defaults['group_id']);

        $this->defaultPassword = $defaults['password'];

        $model = new User;

        $this->tableName = $model->getTable();
        $this->fillable = array_merge($model->getFillable(), $model->getDates());

        parent::__construct($sessionId, $filePath, $defaults, $duration);
    }

    public function loadContents()
    {
        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($this->filePath);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $i => $row) {
                if ($i <= 1) {
                    if ($this->columns != $row) {
                        throw new InvalidSheetException;
                    }

                    continue;
                }

                $this->contents->push([
                    'first_name'    => $row[0],
                    'middle_name'   => $row[1],
                    'last_name'     => $row[2],
                    'email'         => $row[3],
                    'username'      => $row[4],
                    'password'      => $row[5],
                    'group'         => $row[6],
                    'department'    => $row[7],
                    'profile'       => $row[8]
                ]);
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getContents()
    {
        return parent::getContents()
            ->filter(function ($entry) {
                return filter_var($entry['email'], FILTER_VALIDATE_EMAIL);
            })
            ->map(function ($entry) {
                if (!$entry['username']) {
                    $entry['username'] = $this->generateUsername($entry['first_name'], $entry['last_name']);
                }

                if (!$entry['password']) {
                    $entry['password'] = $this->defaultPassword;
                }

                $entry['department'] = $this->getModel('department', $entry['department']);
                $entry['profile'] = $this->getModel('profile', $entry['profile']);
                $entry['group'] = $this->getModel('group', $entry['group']);



                return $entry;
            })
            ->unique('username')
            ->unique('email');
    }

    /**
     * Import spreadsheet contents to database
     */
    public function import()
    {
        $columnString = implode(',', $this->fillable);
        $bindingString = rtrim(str_repeat('?,', count($this->fillable)), ',');

        $query = "INSERT IGNORE INTO {$this->tableName} ({$columnString}) VALUES ";

        DB::beginTransaction();

        $this->getContents()->chunk(100)->each(function ($chunk) use ($query, $bindingString) {
            $values = $chunk->map(function ($entry) use (&$query, &$bindings, $bindingString) {
                $values = [];

                foreach ($this->fillable as $column) {
                    $values[$column] = null;

                    if (array_key_exists($column, $entry)) {
                        $values[$column] = $entry[$column];
                    }

                    if (array_key_exists($column, $this->resolveIds)) {
                        $model = $entry[$this->resolveIds[$column]];

                        if ($model) {
                            $values[$column] = $model->id;
                        }
                    }

                    if ($column == 'password') {
                        $values['password'] = Hash::make($entry['password']);
                    }

                    if ($column == 'require_password_change') {
                        $values['require_password_change'] = 1;
                    }

                    if ($column == 'created_at') {
                        $values['created_at'] = date('Y-m-d H:i:s');
                    }

                    if ($column == 'updated_at') {
                        $values['updated_at'] = date('Y-m-d H:i:s');
                    }
                }

                return $values;
            });

            $query .= rtrim(str_repeat("($bindingString),", $chunk->count()), ',');

            DB::insert($query, $values->flatten()->all());
        });

        DB::commit();
    }

    /**
     * Get model based on name. Returns the default model when not found
     * 
     * @param string $table Model table name
     * @param string $name Model name property
     * 
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getModel($table, $name)
    {
        $collectionName = $table . 's';
        $defaultCollectionName = 'default' . ucfirst($table);

        if (property_exists($this, $collectionName)) {
            $model = $this->{$collectionName}->where('name', $name)->first();
            
            if ($model) {
                return $model;
            }

            if (property_exists($this, $defaultCollectionName)) {
                return $this->{$defaultCollectionName};
            }
        }
    }

    /**
     * Generate username based on first name and last name
     * 
     * @param string $firstName First name
     * @param string $lastName Last name
     * 
     * @return string
     */
    protected function generateUsername($firstName, $lastName)
    {
        $generated = strtolower(substr($firstName, 0, 1) . $lastName);

        return $generated;
    }
}
