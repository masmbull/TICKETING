<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with(['role', 'department'])
            ->withoutTrashed()
            ->orderBy('name')
            ->get();

        $roles = Role::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();

        return view('users.index', compact('users', 'roles', 'departments'));
    }

    public function trashed(Request $request): View
    {
        $users = User::with(['role', 'department', 'deletedBy'])
            ->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('users.trashed', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();

        return view('users.create', compact('roles', 'departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'force_password_change' => ['boolean'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;
        $validated['force_password_change'] = $request->boolean('force_password_change');

        // UserObserver audits the creation.
        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(int $id): View
    {
        $user = User::withTrashed()->with(['role', 'department', 'createdTickets', 'assignedTickets'])->findOrFail($id);

        return view('users.show', compact('user'));
    }

    public function edit(int $id): View
    {
        $user = User::withTrashed()->findOrFail($id);
        $roles = Role::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();

        return view('users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // UserObserver audits the update (incl. role_changed nuance).
        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Soft-delete so ticket / comment / audit history stays intact.
        // The deleted_by save and the delete each fire a UserObserver audit
        // entry ('user_trashed' + 'deleted') with full actor context.
        $user->deleted_by = auth()->id();
        $user->save();
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User moved to trash successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);

        // UserObserver logs the 'user_restored' event.
        $user->restore();

        // Clear the deleter reference now that the user is back.
        $user->forceFill(['deleted_by' => null])->save();

        return redirect()->route('users.trashed')->with('success', 'User restored successfully.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $email = $user->email;

        // UserObserver logs 'user_force_deleted'.
        $user->forceDelete();

        return redirect()->route('users.trashed')->with('success', 'User permanently removed.');
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $status = $user->is_active ? 'activated' : 'deactivated';

        // UserObserver logs 'activated'/'deactivated'.
        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', "User " . $status . " successfully.");
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));

        if (mb_strlen($q) < 1) {
            return response()->json([]);
        }

        $users = User::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$q}%"])
                      ->orWhereRaw('LOWER(email) LIKE LOWER(?)', ["%{$q}%"]);
            })
            ->orderBy('name')
            ->limit(10)
            ->select('id', 'name', 'email')
            ->get();

        return response()->json($users->map(fn (
$user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ])->values());
    }
}