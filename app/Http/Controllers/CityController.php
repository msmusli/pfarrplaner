<?php

namespace App\Http\Controllers;

use App\City;
use Illuminate\Http\Request;

class CityController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $cities = City::all();
        return view('cities.index', compact('cities'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('cities.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        City::create($this->validateRequest());
        return redirect()->route('cities.index')->with('success', 'Die neue Kirchengemeinde wurde gespeichert.');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $city = City::find($id);

        return view('cities.edit', compact('city'));    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\City $city
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(City $city)
    {
        $city->update($this->validateRequest());
        return redirect('/cities')->with('success', 'Die Kirchengemeinde wurde geändert.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\City $city
     * @return \Illuminate\Http\Response
     */
    public function destroy(City $city)
    {
        $city->delete();
        return redirect('/cities')->with('success', 'Die Kirchengemeinde wurde gelöscht.');
    }

    /**
     * Validate a city request
     * @return mixed
     */
    protected function validateRequest()
    {
        return request()->validate([
            'name' => 'required|max:255',
            'public_events_calendar_url' => 'nullable',
            'default_offering_goal' => 'nullable',
            'default_offering_description' => 'nullable',
            'default_funeral_offering_goal' => 'nullable',
            'default_funeral_offering_description' => 'nullable',
            'default_wedding_offering_goal' => 'nullable',
            'default_wedding_offering_description' => 'nullable',
            'op_domain' => 'nullable',
            'op_customer_key' => 'nullable',
            'op_customer_token' => 'nullable',
        ]);
    }

}
