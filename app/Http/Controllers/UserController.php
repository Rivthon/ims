<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage-users');
        $users = User::query()->when(request('q'), function ($query, $search) {
            $query->where(fn ($nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('unit', 'like', "%{$search}%"));
        })->orderBy('name')->paginate(20)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        Gate::authorize('manage-users');

        return view('users.form', ['managedUser' => new User]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        User::create($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        Gate::authorize('manage-users');

        return view('users.form', ['managedUser' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        if ($request->user()->is($user) && ($data['role'] !== 'super_admin' || ! $data['is_active'])) {
            throw ValidationException::withMessages(['role' => 'Anda tidak dapat menurunkan role atau menonaktifkan akun sendiri.']);
        }
        if ($user->role === 'super_admin' && ($data['role'] !== 'super_admin' || ! $data['is_active']) && User::query()->where('role', 'super_admin')->where('is_active', true)->count() === 1) {
            throw ValidationException::withMessages(['role' => 'Super Admin aktif terakhir tidak dapat diubah.']);
        }
        if (blank($data['password'] ?? null)) {
            $data = Arr::except($data, 'password');
        }
        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('manage-users');
        if (request()->user()->is($user)) {
            throw ValidationException::withMessages(['user' => 'Anda tidak dapat menghapus akun sendiri.']);
        }
        if ($user->role === 'super_admin' && User::query()->where('role', 'super_admin')->count() === 1) {
            throw ValidationException::withMessages(['user' => 'Super Admin terakhir tidak dapat dihapus.']);
        }
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
