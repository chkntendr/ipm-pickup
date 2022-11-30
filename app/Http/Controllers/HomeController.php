<?php

namespace App\Http\Controllers;

use App\Models\Pickup;
use App\Models\Client;
use App\Models\Driver;
use App\Imports\ImportPickup;
use App\Exports\PickupExport;
use DataTables;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {

        $tipe   = DB::table('tipe_barang')->get();
        $client = Client::all();
        $count  = Pickup::count();
        $data   = Pickup::paginate(20);
        $berat  = Pickup::sum('berat');
        $jumlah = Pickup::sum('jumlah');
        $driver = Driver::all();
        return view('dashboard.home', compact('data', 'count', 'berat', 'tipe', 'client', 'jumlah', 'driver'));
    }

    public function getTujuan(Request $request) {
        $data = [];

        if($request->has('q')){
            $search = $request->q;
            $data   = Pickup::select("tujuan")
            		->where('tujuan','LIKE',"%$search%")
            		->get();
        }
        return response()->json($data);
    }

    public function getKargo(Request $request) {
        if ($request->ajax()) {

            $data = Pickup::latest()->where('tipe', "Kargo")->get();
            return Datatables::of($data)
                            ->addIndexColumn()
                            ->addColumn('action', function($data){
                            $actionBtn = '<a id="btn-edit-pickup" data-id="'.$data->id.'" type="button" class="edit bi bi-pencil-square" style="color: orange"></a>
                            <a type="button" id="btn-delete-pickup" data-remote="/home/delete/'.$data->id.'" style="color: red" class="delete bi bi-trash"></a>';

                            return $actionBtn;
                            })
                            ->rawColumns(['action'])
                            ->make(true);
        }
    }

    public function getDokumen(Request $request) {
        if ($request->ajax()) {
            $data = Pickup::latest()->where('tipe', "Dokumen")->get();

            return DataTables::of($data)
                            ->addIndexColumn()
                            ->addColumn('action', function($data) {
                                $actionBtn = '<a id="btn-edit-pickup" data-id="'.$data->id.'" type="button" class="edit bi bi-pencil-square" style="color: orange"></a>
                                <a type="button" id="btn-delete-pickup" data-remote="/home/delete/'.$data->id.'" style="color: red" class="delete bi bi-trash"></a>';

                                return $actionBtn;
                            })
                            ->rawColumns(['action'])
                            ->make(true);
        }
    }

    public function store(Request $request) {
        // Create post
        
        $pickup = new Pickup;
        $pickup->tipe       = $request->input('tipe');
        $pickup->client     = $request->input('client');
        $pickup->tujuan     = $request->input('tujuan');
        $pickup->sp1        = $request->input('sp1');
        $pickup->sp2        = $request->input('sp2');
        $pickup->sp3        = $request->input('sp3');
        $pickup->description= $request->input('description');
        $pickup->jumlah     = $request->input('jumlah');
        $pickup->berat      = $request->input('berat');
        $pickup->tanggal    = $request->input('tanggal');
        $pickup->driver     = $request->input('driver');
        $pickup->created_at = \Carbon\Carbon::now(); # new \Datetime()
        $pickup->updated_at = \Carbon\Carbon::now(); # new \Datetime()
        $pickup->save();
        
        return response()->json([
            'status'    => 200,
            'message'   => "Data inserted",
            'data'      => $pickup
        ]);
    }

    public function delete($id) {
        Pickup::where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => "Data berhasil dihapus!"
        ]);
    }

    public function export() {
        return Excel::download(new PickupExport, 'data_pickup.xlsx');
    }

    public function import(Request $request) {
        request()->validate([
            'file'  => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        if ($files  = $request->file('file')) {
            $import = new ImportPickup;
            $import->setStartRow(2);
            Excel::import ($import, $request->file('file')->store('files'));

            return redirect()->back()->with('success', 'Import Berhasil!');
        }
    }

    public function search(Request $request) {
        
    }
}
