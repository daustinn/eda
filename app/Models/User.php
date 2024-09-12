<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $table = 'users';

    protected $perPage = 15;

    protected $fillable = [
        'id',
        'profile',
        'dni',
        'first_name',
        'last_name',
        'email',
        'password',
        'id_role_user',
        'privileges',
        'status',
        'id_role',
        'id_branch',
        'group_schedule_id',
        'supervisor_id',
        'email_access',
        'username',
        'created_by',
        'updated_by',
        'entry_date',
        'exit_date',
        'date_of_birth',
        'default_assist_terminal_id'
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $hidden = [
        'password',
        'remember_token',
    ];


    static $rules = [
        'dni' => 'required|numeric|digits:8',
        'first_name' => 'required',
        'immediate_boss' => 'uuid|nullable',
        'username' => 'required',
        'domain' => 'required',
        'last_name' => 'required',
        'id_role' => 'uuid|required',
        'date_of_birth_day' => 'numeric|nullable',
        'date_of_birth_month' => 'numeric|nullable',
        'date_of_birth_year' => 'numeric|nullable',
        'id_role_user' => 'uuid|required',
        'id_branch' => 'uuid|required',
        'group_schedule_id' => 'uuid',
    ];

    static $organization = [
        'default_assist_terminal' => 'uuid|nullable',
        'id_role' => 'uuid|required',
        'id_branch' => 'uuid|required',
        'group_schedule_id' => 'uuid|nullable',
    ];

    static $rol = [
        'id_role_user' => 'uuid|required',
    ];

    static $details = [
        'dni' => 'required|numeric|digits:8',
        'date_of_birth_day' => 'required|numeric',
        'date_of_birth_month' => 'required|numeric',
        'date_of_birth_year' => 'required|numeric',
        'first_name' => 'required',
        'last_name' => 'required',
    ];

    static $organizationRules = [
        'id_branch' => 'uuid|required',
        'id_role' => 'uuid|required',
    ];

    static $segurityAccess = [
        'username' => 'required',
        'domain' => 'required',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'email_access' => 'array',
        'entry_date' => 'date',
        'exit_date' => 'date',
        'date_of_birth' => 'date',
    ];

    public function defaultTerminal()
    {
        return $this->hasOne(AssistTerminal::class, 'id', 'default_assist_terminal_id');
    }

    public function role_position()
    {
        return $this->hasOne(Role::class, 'id', 'id_role');
    }

    public function job_position()
    {
        return $this->role_position->job_position;
    }

    public function department()
    {
        return $this->role_position->department;
    }

    public function area()
    {
        return $this->role_position->department->area;
    }

    public function branch()
    {
        return $this->hasOne(Branch::class, 'id', 'id_branch');
    }

    public function createdBy()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function updatedBy()
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function role()
    {
        return $this->hasOne(UserRole::class, 'id', 'id_role_user');
    }

    public function privileges()
    {
        return $this->role->privileges;
    }

    public function hasGroup($key)
    {
        return in_array($key, array_map(function ($privilege) {
            return explode(':', $privilege)[0];
        }, $this->role->privileges));
    }

    public function has($key)
    {
        return in_array($key, $this->role->privileges);
    }

    public function isDev()
    {
        return $this->has('development');
    }

    public function groupSchedule()
    {
        return $this->hasOne(GroupSchedule::class, 'id', 'group_schedule_id');
    }

    public function supervisor()
    {
        return $this->hasOne(User::class, 'id', 'supervisor_id');
    }

    public function edas()
    {
        return $this->hasMany(Eda::class, 'id_user', 'id');
    }

    public function names()
    {
        $firstNameParts = explode(' ', trim($this->first_name));
        $firstName = $firstNameParts[0];

        $lastNameParts = explode(' ', trim($this->last_name));

        $lastName = $lastNameParts[0];
        if (count($lastNameParts) > 1) {
            $lastName = implode(' ', array_slice($lastNameParts, 0, -1));
        }
        return $firstName . ' ' . $lastName;
    }

    public function people()
    {
        return $this->hasMany(User::class, 'supervisor_id', 'id');
    }

    public function companions()
    {
        $level = $this->role_position->job_position->level;
        $companions = User::whereHas('role_position', function ($query) use ($level) {
            $query->whereHas('job_position', function ($query) use ($level) {
                $query->where('level', $level);
            });
        })
            ->where('id', '!=', $this->id)
            ->get();

        return $companions;
    }

    public function schedules()
    {
        $schedulesMatched = $this->groupSchedule->schedules;
        $customSchedules = Schedule::where('user_id', $this->id)->get();
        $allSchedules = $schedulesMatched->merge($customSchedules);

        $schedulesGenerated = [];

        foreach ($allSchedules as $schedule) {
            for ($date = Carbon::parse($schedule->start_date); $date->lte(Carbon::parse($schedule->end_date)); $date->addDay()) {

                $dayOfWeek = $date->dayOfWeek + 1;

                if ($dayOfWeek == 8) $dayOfWeek = 1;

                if (in_array((string)$dayOfWeek, $schedule->days)) {

                    $schedulesGenerated[] = [
                        'title' => $schedule->title,
                        'from' => Carbon::parse($schedule->from)->setDate($date->year, $date->month, $date->day)->format('Y-m-d H:i:s'),
                        'to' => Carbon::parse($schedule->to)->setDate($date->year, $date->month, $date->day)->format('Y-m-d H:i:s'),
                    ];
                }
            }
        }

        return $schedulesGenerated;
    }

    public function summarySchedules()
    {
        $schedules = null;

        $schedules = $this->groupSchedule->schedules;
        $customSchedules = Schedule::where('user_id', $this->id)->get();

        $schedules = $schedules->merge($customSchedules);

        return $schedules;
    }
}
