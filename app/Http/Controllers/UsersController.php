<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UsersController extends Controller
{
    
    public function getUsers(Request $request)
    {
    	if ( $request->input('showdata') ) {
    	    return User::orderBy('created_at', 'desc')->get();
            
    	}
        $columns = ['name', 'email', 'created_at'];
        $length = $request->input('length');
        $column = $request->input('column'); 
        $search_input = $request->input('search');

        $query = User::select('name', 'email', 'created_at')->orderBy($columns[$column]);

        if ($search_input) {
            $query->where(function($query) use ($search_input) {
                $query->where('name', 'like', '%' . $search_input . '%')
                ->orWhere('email', 'like', '%' . $search_input . '%')
                ->orWhere('created_at', 'like', '%' . $search_input . '%');
            });
        }

        $users = $query->paginate($length);
        return ['data' => $users];
    }

    public function deleteUser(User $user) {

        if($user) {
            $user->delete();
        }
        return 'user deleted';
    }
}
