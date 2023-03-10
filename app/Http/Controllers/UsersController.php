<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use DataTables;

class UsersController extends Controller
{
    public function get(Request $request) {
        if ($request->ajax()) {
            $data = User::latest()->get();

            return Datatables::of($data)
                            ->addIndexColumn()
                            ->addColumn('action', function($data) {
                                $actionBtn = '<a onclick="editUser()" type="button" class="edit ri-edit-box-line" style="color: orange"></a>
                                <a id="btn-delete-user" data-remote="/users/delete/'.$data->id.'" type="button" style="color: red" class="delete ri-delete-bin-5-line"></a>';
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('users.users');
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
        $user = new User;

        $password = Hash::make($request->password);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $password;
        $user->roles = $request->role;

        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'User ditambahkan!',
            'data'  => $user
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        User::where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berahasil dihapus'
        ]);
    }
}
