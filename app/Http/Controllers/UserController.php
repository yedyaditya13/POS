<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

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


}
