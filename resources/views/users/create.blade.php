@extends('layouts.app')

@section('title', 'Create User - MITO IT Helpdesk')

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center mb-6">
            <a href="{{ route('users.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-2xl font-bold text-slate-900">Create User</h1>
        </div>

        <div class="card">
            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-danger-50 border border-danger-200 rounded-xl">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-danger-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="text-sm text-danger-700">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="form-label form-label-required">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="input" />
                    </div>

                    <div>
                        <label for="email" class="form-label form-label-required">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required class="input" />
                    </div>

                    <div>
                        <label for="password" class="form-label form-label-required">Password</label>
                        <input type="password" name="password" id="password" required class="input" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label form-label-required">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required class="input" />
                    </div>

                    <div>
                        <label for="role_id" class="form-label form-label-required">Role</label>
                        <select name="role_id" id="role_id" required class="select">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="department_id" class="form-label">Department</label>
                        <select name="department_id" id="department_id" class="select">
                            <option value="">No Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="force_password_change" id="force_password_change" value="1"
                               {{ old('force_password_change') ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-500 border-slate-300 rounded focus:ring-primary-500/20" />
                        <label for="force_password_change" class="text-sm text-slate-700">Force password change on first login</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
