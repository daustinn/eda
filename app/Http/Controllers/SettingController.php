<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $req)
    {
        $match = Area::orderBy('created_at', 'desc');
        $query = $req->get('query');

        if ($query) {
            $match->where('code', 'like', '%' . $query . '%')
                ->orWhere('name', 'like', '%' . $query . '%')
                ->get();
        }

        $areas = $match->paginate();

        return view('modules.settings.+page', [
            'areas' => $areas
        ])
            ->with('i', (request()->input('page', 1) - 1) * $areas->perPage());
    }
}
