<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller {
 public function show(){return view('auth.login');}
 public function login(Request $request){$data=$request->validate(['email'=>'required|email','password'=>'required']); if(Auth::attempt($data,$request->boolean('remember'))){$request->session()->regenerate();return redirect()->intended('/dashboard');} return back()->withErrors(['email'=>'Email atau password tidak sesuai.'])->onlyInput('email');}
 public function logout(Request $request){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect('/login');}
}
