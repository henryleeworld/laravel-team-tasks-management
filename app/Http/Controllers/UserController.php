<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\CreateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Create the user controller.
     */
    public function __construct()
    {
        Gate::authorize('manage-users');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::query()
            ->where('id', '!=', auth()->id())
            ->when(auth()->user()->hasRole('team administrator'), function (Builder $query) {
                return $query->where('team_id', auth()->user()->team_id);
            })
            ->paginate();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUserRequest $request): RedirectResponse
    {
        $role = Role::where('name', 'user')->first();

        User::create(array_merge(
            $request->validated(),
            [
                'role_id' => $role->id,
                'team_id' => auth()->user()->team_id,
            ]
        ));

        return redirect()->route('users.index')->with('status', __('User created.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ChangePasswordRequest $request, User $user)
    {
        $user->update([
            'password' => $request['password'],
        ]);

        return redirect()->route('users.index')->with('status', __('Password changed.'));
    }
}
