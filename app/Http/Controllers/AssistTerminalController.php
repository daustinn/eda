<?php

namespace App\Http\Controllers;

use App\Models\AssistTerminal;
use Illuminate\Http\Request;

class AssistTerminalController extends Controller
{
    public function index()
    {
        $assists_databases = AssistTerminal::all();
        return view('modules.assists.terminals.+page', [
            'assists_databases' => $assists_databases
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(AssistTerminal::$rules);
        $data = $request->all();

        $exists = AssistTerminal::where('database_name', $data['database_name'])->first();
        if ($exists) {
            return redirect()->back()->with('error', 'Database name already exists');
        }

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        $terminal = AssistTerminal::create($data);
        return response()->json($terminal, 200);
    }

    public function update(Request $request, $id)
    {
        $terminal = AssistTerminal::find($id);

        if (!$terminal) {
            return response()->json('Terminal not found', 404);
        }

        $request->validate(AssistTerminal::$rules);
        $data = $request->all();

        $exists = AssistTerminal::where('database_name', $data['database_name'])->where('id', '!=', $id)->first();
        if ($exists) {
            return redirect()->back()->with('error', 'Database name already exists');
        }

        $data['updated_by'] = auth()->id();
        $terminal->update($data);

        return response()->json($terminal, 200);
    }
}
