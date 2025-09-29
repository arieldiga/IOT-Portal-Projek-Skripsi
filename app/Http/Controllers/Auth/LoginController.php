<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomUser;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        // terima baik input 'login' maupun 'username' dari form
        $loginInput = $request->input('login', $request->input('username'));

        // debug: cek user exists (jangan log password)
        $userExists = CustomUser::where('username', $loginInput)->exists();
        Log::info('Login attempt for', ['username' => $loginInput, 'exists' => $userExists]);

        $credentials = [
            'username' => $loginInput,
            'password' => $request->password,
        ];

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            Log::info('Login success', ['id' => Auth::id(), 'username' => Auth::user()->username]);
        
            $user = Auth::user();
        
            // Semua role masuk dashboard, role ditentukan di blade
            return redirect()->route('dashboard')
                ->with('success', 'Login berhasil sebagai ' . $user->role . '!');
        }
        

        Log::warning('Login failed', ['username' => $loginInput]);
        return back()->withErrors(['login' => 'Username atau password salah.'])->withInput($request->only('login'));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success','Logout berhasil');
    }
}
