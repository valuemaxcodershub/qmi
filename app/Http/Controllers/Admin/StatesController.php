<?php

namespace App\Http\Controllers\Admin;

use App\Models\States;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class StatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allStates = States::orderBy('state_name', 'asc')->paginate(10);
        return view('admin-views.states.index', compact('allStates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStateView()
    {
        return view('admin-views.states.add');
    }

    public function createState(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'state_name' => "required|string"
        ], [
            'state_name.required' => 'Please enter a state name'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $stateName = $data['state_name'];

        $isExist = States::where(['state_name' => $stateName])->exists();

        if ($isExist) {
            Session::flash('error', "State (".$stateName.") already exists");
            return redirect()->back()->withInput();
        }

        $createState = States::create([
            'state_name' => $stateName
        ]);

        if (!$createState) {
            Session::flash('error', 'Error creating state');
            return redirect()->back();
        }

        Session::flash('success', 'State added successfully as one of our delivery states');
        return redirect()->route('admin.state.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\States  $states
     * @return \Illuminate\Http\Response
     */
    public function show(States $states)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\States  $states
     * @return \Illuminate\Http\Response
     */
    public function edit(States $states)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\States  $states
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, States $states)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\States  $states
     * @return \Illuminate\Http\Response
     */
    public function destroy(States $states, $id)
    {
        $state = $states->find($id);
        if (!$state) {
            Session::flash('error', "State  could not be found");
            return redirect()->route('admin.state.index');
        }

        $state->delete();
        Session::flash('success', "State deleted successfully");
        return redirect()->route('admin.state.index');
    }
}
