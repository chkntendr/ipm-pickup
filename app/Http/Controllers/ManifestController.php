<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Manifest;
use App\Models\Invoice;
use App\Import\ManifestImport;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Carbon\Carbon;
use DataTables;

class ManifestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('manifest.manifest');
    }
    
    public function detail($id, Request $request) {
        if ($request->ajax()) {
            $data = Invoice::where('mnf_id', $id)->get();

            return DataTables::of($data)
                            ->addIndexColumn()
                            // ->addColumn('manifest', function(Invoice $invoice) {
                            //     return $invoice->manifest->map(function($manifest){
                            //         return $invoice->m_id;
                            //     })->implode('<br>');
                            // })
                            ->addColumn('action', function($data) {
                                $actionBtn = '<a id="btn-edit-barInvoice" type="button" data-remote="'.$data->id.'" class="edit ri-edit-box-line" style="color: orange"></a>
                                <a type="button" id="btn-delete-barInvoice" data-remote="/invoice/delete/'.$data->id.'" type="button" style="color: red" class="delete ri-delete-bin-5-line"></a>';
                                return $actionBtn;
                            })
                            ->rawColumns(['action'])
                            ->make(true);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $id = IdGenerator::generate(['table' => 'manifest', 'field' => 'm_id', 'length' => 8, 'prefix' => 'MNF-']);

        $manifest = new Manifest();
        $manifest->m_id = $id;
        $manifest->save();

        return response()->json([
            'status' => 200,
            'message'=> 'Manifest berhasil dibuat!',
            'data'   => $manifest
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request) {
        if ($request->ajax()) {

            $data = Manifest::latest()->get();
            return Datatables::of($data)
                            ->addIndexColumn()
                            ->addColumn('proses', function($data) {
                                if ($data->is_processed == false) {
                                    $processBtn = '
                                    <a class="btn btn-sm btn-warning" id="prosesInv" data-remote="'.$data->id.'">
                                    <i id="prosesIcon" class="bi bi-receipt"></i>
                                    Invoice
                                    </a>';
                                    return $processBtn;
                                } else {
                                    $processBtn = '<i class="bi bi-check-square-fill"></i>';
                                    return $processBtn;
                                }

                            })
                            ->addColumn('action', function($data) {
                                if ($data->total == null) {
                                    $actionBtn = '
                                    <a id="btn-manifest-barcode" data-remote="'.$data->id.'" type="button" class="detail ri-barcode-line" style="color: black"></a>
                                    <a id="btn-detail-manifest" data-remote="'.$data->id.'" type="button" class="edit ri-search-2-line" style="color: orange"></a>
                                    <a type="button" id="btn-delete-manifest" data-remote="/manifest/delete/'.$data->id.'" style="color: red" class="delete ri-delete-bin-5-line"></a>';

                                    return $actionBtn;
                                } else {
                                    $actionBtn = '
                                    <a id="btn-detail-manifest" data-remote="'.$data->id.'" type="button" class="edit ri-search-2-line" style="color: orange"></a>
                                    <a type="button" id="btn-delete-manifest" data-remote="/manifest/delete/'.$data->id.'" style="color: red" class="delete ri-delete-bin-5-line"></a>';

                                    return $actionBtn;
                                }
                            })
                            ->rawColumns(['action', 'proses'])
                            ->make(true);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $manifest = Manifest::find($id);
        $barcode = preg_replace( "/\r|\n/", "", $request->barcode);
        $total = strlen($barcode) / 16;
        $manifest->uploaded_at = \Carbon\Carbon::now();
        $manifest->barcode = $request->barcode;
        $manifest->total = $total;
        $manifest->save();

        return response()->json([
            'status' => 200,
            'message'=> 'Data berhasil diperbarui',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        Manifest::where('id', $id)->delete();
        Invoice::where('mnf_id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berahasil dihapus'
        ]);

    }

    public function upload(Request $request) {
        request()->validate([
            'file'  => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        if ($files  = $request->file('file')) {
            $import = new ManifestImport;
            Excel::import ($import, $request->file('file')->store('files'));

            return response()->json([
                'status'    => 200,
                'message'   => 'Data berhasil di upload',
                'data'      => $import
            ]);
        }
    }

    public function invoice(Request $request, $id) {
        $done = $request->is_processed;
        $cek_resi = Manifest::where('id', $id)->count('total');
        if ($cek_resi > 0) {
            Manifest::where('id', $id)
                            ->update([
                                'is_processed' => $done
                            ]);
            return response()->json([
                'status' => 200,
                'message' => 'Manifest OK',
            ]);
        } else {
            return response()->json([
                'status' => 403,
                'message'=> 'Belum ada resi!',
            ]);
        }
        // $invoice = Manifest::where('id', $id)->select('id', 'is_processed')->get();
    }

    public function getM_id($id) {
        $m_id = Manifest::where('id', $id)->select('m_id')->get();

        return response()->json([
            'status' => 200,
            'message' => 'Data fetched!',
            'data' => $m_id
        ]);
    }

    public function getBarcode($id) {
        $barcode = Manifest::where('id', $id)->select('m_id')->get();

        return response()->json([
            'status' => 200,
            'message' => 'Data fetched!',
            'data' => $barcode
        ]);
    }

    public function loadInvoice() {
        $invoice = Manifest::get();

        return response()->json([
            'data' => $invoice
        ]);
    }
}
