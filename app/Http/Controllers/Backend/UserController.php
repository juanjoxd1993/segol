<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index() {
    	return view('backend.login');
    }

    public function login() {
    	$messages = [
    		'user.required'	=> 'El usuario es obligatorio',
    		'password.required'	=> 'La contraseña es obligatoria'
    	];

    	$rules = [
    		'user'		=> 'required',
    		'password'	=> 'required'
    	];

    	$this->validate(request(), $rules, $messages);

    	if (Auth::attempt([
    			'user'		=> request('user'),
    			'password'	=> request('password')
    		], true)) {
    		$type = 3;
    		$title = 'Ok!';
    		$msg = 'Bienvenido, ' . Auth::user()->name;
    		$url = route('dashboard.voucher.send_ose');
    	} else {
            $type = 2;
            $title = "Error!";
            $msg = "Verifique los datos ingresados";
            $url = "";
        }

        return response()->json([
            'type'  => $type,
            'title' => $title,
            'msg'   => $msg,
            'url'   => $url
        ], 200);
    }

    public function logout() {
        Auth::logout();
        return redirect()->route('login');
    }
}
