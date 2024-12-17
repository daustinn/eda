<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\User;
use App\Models\UserAudit;
use App\services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $imageUploadService, $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public static function relationShipUsers($relationShiptQuery, $limit = null)
    {
        $match = User::orderBy('created_at', 'desc');
        $includes = explode(',', $relationShiptQuery);
        if (in_array('role', $includes)) $match->with('role');
        if (in_array('role.job', $includes)) $match->with('role.job');
        if (in_array('role.department', $includes))  $match->with('role.department');
        if (in_array('role.department.area', $includes)) $match->with('role.department.area');
        if (in_array('manager', $includes)) $match->with('manager');
        if (in_array('manager.job', $includes)) $match->with('manager.job');
        if (in_array('manager.department', $includes))  $match->with('manager.department');
        if (in_array('manager.department.area', $includes)) $match->with('manager.department.area');
        if (in_array('schedules', $includes)) $match->with('schedules');
        if (in_array('schedules.terminal', $includes)) $match->with('schedules.terminal');
        if (in_array('userRole', $includes)) $match->with('userRole');
        if (in_array('contractType', $includes)) $match->with('contractType');
        if ($limit) $match->limit($limit);
        return $match;
    }

    public static function relationShipUser($relationShiptQuery, $queryMatch)
    {
        $match = User::where('username', $queryMatch)->orWhere('id', $queryMatch)->orWhere('email', $queryMatch);
        $includes = explode(',', $relationShiptQuery);
        if (in_array('role', $includes)) $match->with('role');
        if (in_array('role.job', $includes)) $match->with('role.job');
        if (in_array('role.department', $includes))  $match->with('role.department');
        if (in_array('role.department.area', $includes)) $match->with('role.department.area');
        if (in_array('manager', $includes)) $match->with('manager');
        if (in_array('manager.role', $includes)) $match->with('manager.role');
        if (in_array('manager.role.job', $includes)) $match->with('manager.role.job');
        if (in_array('manager.role.department', $includes))  $match->with('manager.role.department');
        if (in_array('manager.role.department.area', $includes)) $match->with('manager.role.department.area');
        if (in_array('schedules', $includes)) $match->with('schedules');
        if (in_array('schedules.terminal', $includes)) $match->with('schedules.terminal');
        if (in_array('userRole', $includes)) $match->with('userRole');
        if (in_array('contractType', $includes)) $match->with('contractType');
        return $match;
    }

    public static function getUser($slug)
    {
        $user = User::where('username', $slug)->orWhere('id', $slug)->orWhere('email', $slug)->first();
        return $user;
    }

    public function all(Request $req)
    {
        $match = $this->relationShipUsers($req->query('relationship'), $req->query('limit'));
        $q = $req->query('q');
        $limit = $req->query('limit');
        $job = $req->query('job');
        $status = $req->query('status');
        $role = $req->query('role');
        $area = $req->query('area');
        $hasManager = $req->query('hasManager');
        $hasSchedules = $req->query('hasSchedules');

        // filters
        if ($status && $status == 'actives') $match->where('status', true);
        if ($status && $status == 'inactives') $match->where('status', false);

        if ($role) $match->where('roleId', $role);

        if ($job) $match->whereHas('role', function ($q) use ($job) {
            $q->where('jobId', $job);
        });

        if ($area) $match->whereHas('role', function ($q) use ($area) {
            $q->whereHas('department', function ($q) use ($area) {
                $q->where('areaId', $area);
            });
        });

        if ($hasManager) {
            if ($hasManager === 'has') $match->whereNotNull('managerId');
            if ($hasManager === 'not') $match->whereNull('managerId');
        }

        if ($hasSchedules) {
            if ($hasSchedules === 'has') $match->whereHas('schedules');
            if ($hasSchedules === 'not') $match->whereDoesntHave('schedules');
        }

        if ($q) $match->where('fullName', 'like', '%' . $q . '%')
            ->orWhere('documentId', 'like', '%' . $q . '%')
            ->orWhere('email', 'like', '%' . $q . '%');

        // pagination or limit
        $users = $limit ? $match->limit($limit)->get() : $match->paginate();

        return response()->json($users);
    }

    public function create(Request $req)
    {
        request()->validate(User::$rules);

        $existsDocumentId = User::where('documentId', $req->documentId)->exists();
        if ($existsDocumentId) {
            return response()->json('El usuario con el documento ingresado ya existe', 400);
        }

        $existsUsername = User::where('username', $req->username)->exists();
        if ($existsUsername) {
            return response()->json('El nombre de usuario ingresado ya esta en uso', 400);
        }

        $existsEmail = User::where('email', $req->email)->exists();
        if ($existsEmail) {
            return response()->json('El correo ingresado ya esta en uso por otra cuenta', 400);
        }

        // validate manage
        if ($req->managerId) {
            $role = Role::find($req->roleId);
            $manage = User::find($req->managerId);
            if ($manage->role->job->level >= $role->job->level) //<-- El jefe inmediato no puede ser de un nivel inferior al usuario. el nivel 0 es el mas alto
                return response()->json('El jefe inmediato no puede ser de un nivel inferior al usuario', 400);
        }

        $fullName = $req->firstNames . ' ' . $req->lastNames;

        $user = User::create([
            'photoURL' =>  $req->photoURL,
            'documentId'  => $req->documentId,
            'fullName' => $fullName,
            'firstNames' => $req->firstNames,
            'lastNames' => $req->lastNames,
            'email' => $req->email,
            'password' => bcrypt($req->password),
            'roleId' => $req->roleId,
            'userRoleId' => $req->userRoleId,
            'entryDate' => $req->entryDate,
            'contractTypeId' => $req->contractTypeId,
            'status' => $req->status,
            'username' => $req->username,
            'managerId' => $req->managerId,
            'birthdate' => $req->birthdate,
            'displayName' => $req->displayName,
            'contacts' => $req->contacts ? $req->contacts : null,
            'createdBy' => Auth::id(),
        ]);

        $schedules = collect($req->schedules)->toArray();

        foreach ($schedules as $schedule) {
            Schedule::create([
                'userId' => $user->id,
                'from' =>  Carbon::parse($schedule['from']),
                'to' => Carbon::parse($schedule['to']),
                'days' => $schedule['days'],
                'title' => $schedule['title'] ?? '-',
                'assistTerminalId' => $schedule['assistTerminalId'],
                'startDate' => Carbon::parse($schedule['startDate']),
                'createdBy' => Auth::id(),
            ]);
        }

        $this->auditService->registerAudit('Usuario creado', 'Se ha creado un usuario', 'users', 'create', $req);
        return response()->json($user);
    }

    public function updateAccount(Request $req, $slug)
    {
        $user =  $this->getUser($slug);
        $req->validate([
            'managerId' => 'nullable|exists:users,id',
            'userRoleId' => 'required|exists:user_roles,id',
            'username' => 'required|string',
            'displayName' => 'required|string',
            'status' => 'required|boolean',
            'customPrivileges' => 'nullable',
            'email' => 'required|email',
        ]);

        $existsUsername = User::where('username', $req->username)->where('id', '!=', $user->id)->exists();
        if ($existsUsername) {
            return response()->json('El nombre de usuario ingresado ya esta en uso', 400);
        }

        $existsEmail = User::where('email', $req->email)->where('id', '!=', $user->id)->exists();
        if ($existsEmail) {
            return response()->json('El correo ingresado ya esta en uso por otra cuenta', 400);
        }

        if ($req->managerId) {
            $role = Role::find($user->roleId);
            $manager = User::find($req->managerId);

            if ($manager->role->job->level <= $role->job->level)
                return response()->json('El jefe inmediato no puede ser de un nivel inferior al usuario', 400);
        }

        $user->email = $req->email;
        $user->userRoleId = $req->userRoleId;
        $user->status = $req->status;
        $user->username = $req->username;
        $user->managerId = $req->managerId;
        $user->customPrivileges = $req->customPrivileges;
        $user->displayName = $req->displayName;
        $user->updatedBy = Auth::id();
        $user->save();

        return response()->json($user);
    }

    public function one(Request $req, $math)
    {
        $user = $this->relationShipUser($req->query('relationship'), $math)->first();
        return response()->json($user);
    }

    public function manager(Request $req, $slug)
    {
        $user =  $this->getUser($slug);
        if ($req->managerId) {
            $manager = User::find($req->managerId);
            $user->managerId = $req->managerId;
            if ($manager->role->job->level > $user->role->job->level)
                return response()->json('El jefe no puede ser de un nivel inferior al usuario', 400);
        } else $user->managerId = null;
        $user->save();
        return response()->json('Ok');
    }

    public function resetPassword($slug)
    {
        $user =  $this->getUser($slug);

        $chars = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789';
        $newPassword = '';
        for ($x = 0; $x < 8; $x++) {
            $i = rand(0, strlen($chars) - 1);
            $newPassword .= substr($chars, $i, 1);
        }

        $user->password = bcrypt($newPassword);
        $user->save();

        return response()->json($newPassword, 200);
    }

    public function toggleStatus($slug)
    {
        $user =  $this->getUser($slug);
        $user->status = !$user->status;
        $user->save();
        return response()->json(
            $user->status ? 'Usuario activado' : 'Usuario desactivado',
            200
        );
    }

    public function schedules(Request $req, $slug)
    {
        $user =  $this->getUser($slug);
        $archived = $req->query('archived') ? true : false;
        $match = Schedule::where('userId', $user->id)->orderBy('created_at', 'desc')->where('archived', $archived);

        $limit = $req->query('limit', 10);
        $includes = explode(',', $req->query('relationship'));
        if (in_array('terminal', $includes)) $match->with('terminal');
        $schedules = $match->limit($limit)->get();
        return response()->json($schedules);
    }

    public function getManager(Request $req, $slug)
    {
        $user =  $this->getUser($slug);
        $manager = User::find($user->id)->manager;
        if (!$manager) return response()->json(null, 200);
        $includes = explode(',', $req->query('relationship'));
        if (in_array('role', $includes)) $manager->with('role');
        if (in_array('role.job', $includes)) $manager->with('role.job');
        if (in_array('role.department', $includes))  $manager->with('role.department');
        if (in_array('role.department.area', $includes)) $manager->with('role.department.area');
        if (in_array('manager', $includes)) $manager->with('manager');
        if (in_array('userRole', $includes)) $manager->with('userRole');
        return response()->json($manager);
    }

    public function downOrganization(Request $req, $slug)
    {
        $user =  $this->getUser($slug);

        $level = $user->role->job->level;

        $matchCoworkers = $this->relationShipUsers($req->query('relationshipCoworkers'), $req->query('limitCoworkers'));
        $matchSubordinates = $this->relationShipUsers($req->query('relationshipSubordinates'), $req->query('limitSubordinates'));
        $matchManager = $this->relationShipUser($req->query('relationshipManager'), $user->managerId);

        // Subordinates conditions
        $matchSubordinates->where('managerId', $user->id);

        // Coworkers conditions
        $matchCoworkers->whereHas('role', function ($query) use ($level) {
            $query->whereHas('job', function ($query) use ($level) {
                $query->where('level', $level);
            });
        })
            ->where('id', '!=',  $user->id);

        $subordinates = $matchSubordinates->get();
        $coworkers = [];

        if ($req->query('dinamic', '') == 'true') {
            $coworkers = $subordinates->count() < 3 ? $matchCoworkers->get() : [];
        }

        return response()->json([
            'coworkers' => $coworkers,
            'subordinates' => $subordinates,
            'manager' => $matchManager->first(),
        ]);
    }

    public function organization(Request $req, $slug)
    {
        $user =  $this->getUser($slug);
        $limitCoworkers = $req->query('limitCoworkers');
        $limitSubordinates = $req->query('limitSubordinates', 10);
        $user->role;
        $currentUser = $user;
        $user->coworkers = $user->coworkers($limitCoworkers);
        $user->subordinates = $user->subordinates->take($limitSubordinates);

        foreach ($user->subordinates as $subordinate) {
            $subordinate->role;
        }

        foreach ($user->coworkers as $coworker) {
            $coworker->role;
        }

        while ($currentUser->manager) {
            $manager = $currentUser->manager;

            foreach ($manager->getAttributes() as $key => $value) {
                if (!array_key_exists($key, $currentUser->getAttributes())) {
                    $currentUser->$key = $value;
                }
            }

            $currentUser = $manager;

            if ($manager->role) {
                $currentUser->role = $manager->role;
            }
        }

        return response()->json($user);
    }

    public function updateOrganization(Request $req, $slug)
    {
        $user =  $this->getUser($slug);

        $user->roleId = $req->roleId;
        $user->contractTypeId = $req->contractTypeId;
        $user->entryDate = $req->entryDate;
        $user->save();

        return response()->json('User updated');
    }

    public function updateProperties(Request $req, $slug)
    {
        $user =  $this->getUser($slug);

        $req->validate([
            'documentId' => 'required|string',
            'firstNames' => 'required|string',
            'lastNames' => 'required|string',
            'birthdate' => 'required|date',
            'contacts' => 'nullable|array',
        ]);

        $alreadyDocumentId = User::where('documentId', $req->documentId)->where('id', '!=', $user->id)->exists();
        if ($alreadyDocumentId) {
            return response()->json('El documento de identidad ya esta en uso', 400);
        }

        $fullName = $req->firstNames . ' ' . $req->lastNames;

        $user->documentId = $req->documentId;
        $user->firstNames = $req->firstNames;
        $user->lastNames = $req->lastNames;
        $user->fullName = $fullName;
        $user->birthdate = $req->birthdate;
        $user->contacts = $req->contacts ? $req->contacts : null;
        $user->save();

        return response()->json('User updated');
    }

    public function createVersion(Request $req, $slug)
    {
        $user =  $this->getUser($slug);
        $user->version = $req->version;
        $user->save();
        return response()->json('Version updated');
    }
}