<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AirportModel;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\error;

class AirportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return "zimin lokh, a u Gomarnika vse rabotaet";
        $data = AirportModel::all();
        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            "name" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'errors' => $validator->errors()
                ]
            ]);
        }

        $dataModel = [
            "name" => $data["name"],
            "city" => $data["city"],
        ];
        AirportModel::create($request->all());

        return response()->json([
            "data" => "Record created " . $data["name"]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
