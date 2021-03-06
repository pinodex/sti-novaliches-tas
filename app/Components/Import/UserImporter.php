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

use Illuminate\Support\Str;
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
     * @var array Sets of accented characters and their ASCII equivalents
     */
    protected $accents = [
        'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
        'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
        'Ï'=>'I', 'Ñ'=>'N', 'Ń'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a',
        'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i',
        'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ń'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o',
        'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
        'ă'=>'a', 'î'=>'i', 'â'=>'a', 'ș'=>'s', 'ț'=>'t', 'Ă'=>'A', 'Î'=>'I', 'Â'=>'A', 'Ș'=>'S', 'Ț'=>'T',
    ];

    /**
     * @var array Columns to resolve ID from model
     */
    protected $resolveIds = [
        'department_id' => 'department',
        'profile_id'    => 'profile',
        'group_id'      => 'group'
    ];

    public function __construct($sessionId, $filePath, $defaults = [], $options = [], $duration = 60)
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

        parent::__construct($sessionId, $filePath, $defaults, $options, $duration);
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
        $normalize = false;

        if (array_key_exists('normalize_data', $this->options)) {
            $normalize = $this->options['normalize_data'] === true;
        }

        return parent::getContents()
            ->map(function ($entry) use ($normalize) {
                if (!$entry['username']) {
                    $entry['username'] = $this->generateUsername($entry['first_name'], $entry['last_name']);
                }

                if (!$entry['password']) {
                    $entry['password'] = $this->defaultPassword;
                }

                $entry['department'] = $this->getModel('department', $entry['department']);
                $entry['profile'] = $this->getModel('profile', $entry['profile']);
                $entry['group'] = $this->getModel('group', $entry['group']);

                if ($normalize) {
                    $entry['first_name'] = $this->properText($entry['first_name']);
                    $entry['middle_name'] = $this->properText($entry['middle_name']);
                    $entry['last_name'] = $this->properText($entry['last_name']);

                    $entry['email'] = $this->normalizeAccents(str_replace(' ', '', $entry['email']));

                    $entry['username'] = str_slug($entry['username']);
                    $entry['password'] = str_slug($entry['password']);
                }

                return $entry;
            })
            ->filter(function ($entry) {
                return filter_var($entry['email'], FILTER_VALIDATE_EMAIL);
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
                        $values['created_at'] = now();
                    }

                    if ($column == 'updated_at') {
                        $values['updated_at'] = now();
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

    /**
     * Convert text to proper text
     * 
     * @param string $text Input text
     * 
     * @return string
     */
    protected function properText($text)
    {
        return Str::title(Str::lower($text));
    }

    /**
     * Convert accented characters to their ASCII equivalents
     * 
     * @param string $text Input text
     * 
     * @return string
     */
    protected function normalizeAccents($text)
    {
        return strtr($text, $this->accents);
    }
}
