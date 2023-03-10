<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;

class ClientController extends Controller
{
    public function index() {
        return view('users.client');
    }

    public function get(Request $request) {
        if ($request->ajax()) {
            $data = Client::latest()->get();

            return Datatables::of($data)
                            ->addIndexColumn()
                            ->addColumn('action', function($data) {
                                $actionBtn = '<a onclick="editPickup()" type="button" class="edit ri-edit-box-line" style="color: orange"></a>
                                <a id="btn-delete-client" data-remote="/client/delete/'.$data->id.'" type="button" style="color: red" class="delete ri-delete-bin-5-line"></a>';
                                return $actionBtn;
                            })
                            ->addColumn('formatedDate', function($data) {
                                $formatedDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y H:i:s');
                                return $formatedDate;
                            })
                            ->rawColumns(['action', 'formatedDate'])
                            ->make(true);
        }
    }

    public function select2(Request $request) {
        $data = [];

        if($request->has('q')){
            $search = $request->q;
            $data   = Client::select("id", "kode_client", "client")
            		->where('kode_client','LIKE',"%$search%")
            		->get();
        }
        return response()->json($data);
    }

    public function create(Request $request) {
        // Create post
        $client = new Client;
        $client->kode_client    = $request->kode_client;
        $client->client         = $request->client;
        $client->created_at     = \Carbon\Carbon::now(); # new \Datetime()
        $client->updated_at     = \Carbon\Carbon::now(); # new \Datetime()
        $client->save();

        return response()->json([
            'status'    => 200,
            'message'   => "Client ditambahkan!",
            'data'      => $client
        ]);
    }

    public function delete($id) {
        $client = Client::where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => "Data berhasil dihapus!"
        ]);
    }
}
