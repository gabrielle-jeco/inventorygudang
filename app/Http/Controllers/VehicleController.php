<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use RealRashid\SweetAlert\Facades\Alert;

class VehicleController extends Controller
{
    public function index()
    {
        return view('vehicles.index');
    }

    public function data()
    {
        $vehicles = Vehicle::query();

        return DataTables::of($vehicles)
            ->addIndexColumn()
            ->addColumn('action', function ($vehicle) {
                return '
                    <a href="javascript:void(0)" data-id="'.$vehicle->id.'" class="btn btn-warning edit-vehicle" style="padding: 8px 16px; margin: 0 4px;"><i class="fa fa-edit"></i> Edit</a>
                    <a href="javascript:void(0)" data-id="'.$vehicle->id.'" class="btn btn-danger delete-vehicle" style="padding: 8px 16px; margin: 0 4px;"><i class="fa fa-trash"></i> Delete</a>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|unique:vehicles,plate_number',
            'make' => 'required',
            'model' => 'required',
        ]);

        Vehicle::create($request->all());

        Alert::success('Success', 'Vehicle added successfully');
        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        $vehicle = Vehicle::find($id);
        return response()->json($vehicle);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'plate_number' => 'required|unique:vehicles,plate_number,'.$id,
            'make' => 'required',
            'model' => 'required',
        ]);

        $vehicle = Vehicle::find($id);
        $vehicle->update($request->all());

        Alert::success('Success', 'Vehicle updated successfully');
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::find($id);
        $vehicle->delete();

        Alert::success('Success', 'Vehicle deleted successfully');
        return response()->json(['success' => true]);
    }
}
