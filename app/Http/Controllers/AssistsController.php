<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\AssistTerminal;
use App\Models\Department;
use App\Models\GroupSchedule;
use App\Models\User;
use App\services\AssistsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AssistsController extends Controller
{
    protected $assistsService;

    public function __construct(AssistsService $assistsService)
    {
        $this->assistsService = $assistsService;
    }

    public function index(Request $request, $isExport = false)
    {

        $queryTerminals = $request->get('terminals') ? explode(',', $request->get('terminals')) : null;
        $startDate = $request->get('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $match = User::orderBy('created_at', 'desc');

        $area_id = $request->get('area');
        $department_id = $request->get('department');

        if ($area_id) {
            $match->whereHas('role_position', function ($q) use ($area_id) {
                $q->whereHas('department', function ($qq) use ($area_id) {
                    $qq->where('id_area', $area_id);
                });
            });
        }

        if ($department_id) {
            $match->whereHas('role_position', function ($q) use ($department_id) {
                $q->where('id_department', $department_id);
            });
        }


        $areas = Area::all();
        $departments = Department::all();

        if ($area_id) {
            $departments = Department::where('id_area', $area_id)->get();
        }

        $users = [];

        if ($department_id || $area_id) {

            $users =  $match->get();
        }

        $schedules = [];

        foreach ($users as $user) {
            $schedules = array_merge($schedules, $this->assistsService->assistsByUser($user->id, $queryTerminals, $startDate, $endDate));
        }

        $terminals = AssistTerminal::all();

        return view('modules.assists.+page', [
            'areas' => $areas,
            'departments' => $departments,
            'users' => $users,
            'schedules' => $schedules,
            'terminals' => $terminals,
        ]);
    }

    public function snSchedules(Request $request)
    {
        $terminals = $request->get('terminals') ? explode(',', $request->get('terminals')) : null;
        $startDate = $request->get('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $query = $request->get('query');

        $assists = Collect([]);
        $perPage = 25;
        $currentPage = $request->get('page', 1);

        if ($query && ($startDate  || $endDate)) {
            $allAssists = $this->assistsService->assists($query, $terminals, $startDate, $endDate);
            $assists = $allAssists->forPage($currentPage, $perPage);
        }

        $terminals = AssistTerminal::all();

        $paginatedAssists = new LengthAwarePaginator(
            $assists,
            isset($allAssists) ? $allAssists->count() : 0,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view(
            'modules.assists.sn-schedules.+page',
            [
                'terminals' => $terminals,
                'assists' => $paginatedAssists
            ]
        );
    }

    public function peerSchedule(Request $request, $isExport = false)
    {
        $queryTerminals = $request->get('terminals') ? explode(',', $request->get('terminals')) : null;
        $startDate = $request->get('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $query = $request->get('query');
        $group = $request->get('group');

        $users = [];

        if ($query) {
            $users = $this->assistsService->employee($query, $queryTerminals);
        }

        $allSchedules = collect([]);

        $perPage = 25;
        $currentPage = $request->get('page', 1);

        if ($group) {
            foreach ($users as $user) {
                $allSchedules = $allSchedules->concat($this->assistsService->assistsByEmployee($user, $group, $queryTerminals, $startDate, $endDate));
            }
        }
        $groups = GroupSchedule::all();
        $terminals = AssistTerminal::all();

        $schedules = $allSchedules->forPage($currentPage, $perPage);

        $paginatedSchedules = new LengthAwarePaginator(
            $schedules,
            isset($allSchedules) ? $allSchedules->count() : 0,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($isExport) {
            return $allSchedules;
        }

        return view(
            'modules.assists.peer-schedule.+page',
            [
                'terminals' => $terminals,
                'schedules' => $paginatedSchedules,
                'groups' => $groups,
                'users' => $users,
            ]
        );
    }

    public function peerScheduleExport(Request $request)
    {
        return $this->peerSchedule($request, true);
    }
}
