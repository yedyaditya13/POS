<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use DB;

class UserController extends Controller
{
    public function index() {
        $users = User::orderBy('created_at', 'DESC')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create() {
        $role = Role::orderBy('created_at', 'ASC')-get();
        return view('users.create', compact('role'));
    }

    public function store(Request $request) {
        $this->validate($request, [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|string|exists:roles, name'
        ]);

        $user = User::firstOrCreate([
            'email' => $request->email
        ],[
            'name' => $request->name,
            'password' => bcrypt($request->password),
            'status' => true
        ]);

        $user->assignRole($request->role);

        return redirect(route('user.index'))->with(['success' => 'User: <strong> ' . $user->name . '</strong> Ditambahkan']);
    }


    public function edit($id) {
        $user = User::findOrFail($id);

        return view('user.edit', compact('user'));
    }


    public function update(Request $request, $id) {
        $this->validate($request, [
            'name' => 'required|string|max:100',
            'email' => 'required|email|exists:users, email',
            'password' => 'nullable|min:6',
        ]);

        $user = User::findOrFail($id);
        $password = !empty($request->password) ? bcrypt($request->password):$user->password;
        $user->update([
            'name' => $request->name,
            'password' =>$password
        ]);

        return redirect(route('user.index'))->with(['success' => 'User: <strong>' . $user->name . '</strong> Diperbaharui']);
    }


    public function destroy($id) {
        $user = findOrFail($id);
        $user->delete();

        return redirect()->back()->with(['success' => 'User: <strong>' . $user->name . '</strong> Dihapus']);
    }

    public function rolePermission(Request $request) {
        $role = $request->get('role');

        // Default, set dua buah variabel dengan nilai null
        $permissions = null;
        $hasPermission = null;

        // Mengambil data role
        $roles = Role::all()->pluck('name');

        // Apabila parameter role terpenuhi
        if (!empty($role)) {
            // select role bedasarkan namenya, ini sejenis dengan method find()
            $getRole = Role::findByName($role);

            // Query untuk mengambil permission yang telah dimiliki oleh role terkait
            $hasPermission = DB::table('role_has_permission')
                ->select('permission.name')
                ->join('permission', 'role_has_permission.permission_id', '=', 'permission.id')
                -where('role_id', $getRole->id)->get()->pluck('name')->all();

            // Mengambil data permission
            $permissions = Permission::all()->pluck('name');

            return view('users.role_permission', compact('roles', 'permissions', 'hasPermission'));
        }
    }

    public function addPermission(Request $request) {
        $this->validate($request, [
            'name' => 'required|string|unique:permissions'
        ]);

        $permission = Permission::firstOrCreate([
            'name' => $request->name
        ]);

        return redirect()->back();
    }

    public function userRolePermission(Request $request, $role) {
        // select role berdasarkan namanya
        $role = Role::findByName($role);

        // Fungsi syncPermission akan menghapus semua permission yang dimiliki role tersebut
        // kemudian di -assign kembali sehingga tidak terjadi duplicate
        $role->syncPermission($request->permission);
        return redirect()->back()->with(['success' => 'Permission to Role Saved!']);
    }

    public function roles(Request $request, $id) {
        $user = User::findOrFail($id);
        $roles = Role::all()->pluck('name');

        return view('users.roles', compact('user', 'roles'));
    }

    public function setRole(Request $request, $id) {
        $this->validate($request, [
            'role' => 'required'
        ]);

        $user = User::findOrFail($id);

        // Menggunakan snycRoles agar telebih dahulu menghapus semua role yang di miliki
        // Kemudian di-set kembali agar tidak terjadi duplicate
        $user->sncyRoles($request->role);
        return redirect()->back()->with(['success' => 'Role Sudah Di Set']);
    }
}
