<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Role-specific login configuration.
     */
    private array $roles = [
        'admin' => [
            'slug' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@mito.local',
            'color' => 'bg-purple-600',
            'hoverColor' => 'hover:bg-purple-700',
            'textColor' => 'text-purple-600',
        ],
        'manager' => [
            'slug' => 'manager',
            'name' => 'IT Manager',
            'email' => 'manager.it@mito.local',
            'color' => 'bg-blue-600',
            'hoverColor' => 'hover:bg-blue-700',
            'textColor' => 'text-blue-600',
        ],
        'support' => [
            'slug' => 'support',
            'name' => 'IT Support',
            'email' => 'shohibul@mito.local',
            'color' => 'bg-green-600',
            'hoverColor' => 'hover:bg-green-700',
            'textColor' => 'text-green-600',
        ],
        'employee' => [
            'slug' => 'employee',
            'name' => 'Employee',
            'email' => 'daniel@mito.local',
            'color' => 'bg-amber-600',
            'hoverColor' => 'hover:bg-amber-700',
            'textColor' => 'text-amber-600',
        ],
    ];

    /**
     * Show the welcome/landing page.
     */
    public function showWelcome(): View
    {
        return view('welcome');
    }

    /**
     * Show the role-specific login form.
     */
    public function showLoginForm(?string $role = null): View
    {
        $config = $this->roles[$role] ?? null;

        if (!$config) {
            return redirect('/');
        }

        return view('auth.login', [
            'roleSlug' => $config['slug'],
            'roleName' => $config['name'],
            'roleColor' => $config['color'],
            'roleHoverColor' => $config['hoverColor'],
            'roleTextColor' => $config['textColor'],
            'demoEmail' => $config['email'],
        ]);
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');
        $roleSlug = $request->input('role');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = auth()->user();

            // Update last_login_at
            $user->update(['last_login_at' => now()]);

            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact administrator.',
                ])->withInput($request->only('email', 'remember'));
            }

            // If role-specific login, verify user has that role
            if ($roleSlug) {
                $role = Role::where('slug', $roleSlug)->first();
                if ($role && $user->role_id !== $role->id) {
                    Auth::logout();
                    return back()->withErrors([
                        'email' => "This account does not have the {$role->name} role.",
                    ])->withInput($request->only('email', 'remember'));
                }
            }

            // Force password change check
            if ($user->force_password_change) {
                return redirect()->route('password.change')
                    ->with('warning', 'You must change your password before continuing.');
            }

            return redirect()->intended('/dashboard');
        }

        return back()
            ->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])
            ->withInput($request->only('email', 'remember'));
    }

    /**
     * Show the change password form (forced).
     */
    public function showChangePasswordForm(): View
    {
        return view('auth.change-password');
    }

    /**
     * Handle the forced password change.
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->password),
            'force_password_change' => false,
        ]);

        return redirect('/dashboard')->with('success', 'Password changed successfully.');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}