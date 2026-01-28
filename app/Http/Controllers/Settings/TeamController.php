<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Enums\MembershipRole;
use App\Http\Requests\Settings\TeamMemberRoleUpdateRequest;
use App\Http\Requests\Settings\TeamMemberStoreRequest;
use App\Models\Account;
use App\Models\Membership;
use App\Models\User;
use App\Notifications\TeamMemberInvited;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function edit(Request $request): Response
    {
        $account = $request->user()->currentAccount;

        $members = $account->users()
            ->withPivot('role')
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.email']);

        return Inertia::render('settings/Team', [
            'account' => $account->only(['id', 'name']),
            'members' => $members->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->pivot?->role instanceof MembershipRole
                    ? $user->pivot->role->value
                    : $user->pivot?->role,
            ]),
            'currentRole' => $request->user()->membershipRoleFor($account)?->value,
            'roleOptions' => MembershipRole::options(),
            'currentUserId' => $request->user()->id,
        ]);
    }

    public function store(TeamMemberStoreRequest $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->ensureOwner($request->user(), $account);

        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'current_account_id' => $account->id,
        ]);

        $user->assignRole('Customer');

        Membership::create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'role' => MembershipRole::from($data['role']),
        ]);

        $user->notify(new TeamMemberInvited($account, $request->user()));

        return back();
    }

    public function updateRole(TeamMemberRoleUpdateRequest $request, User $user): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->ensureOwner($request->user(), $account);

        $membership = Membership::query()
            ->where('account_id', $account->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $role = MembershipRole::from($request->validated('role'));

        if ($user->id === $request->user()->id && $role !== MembershipRole::Owner) {
            throw ValidationException::withMessages([
                'role' => 'Vous devez rester dirigeant sur votre compte.',
            ]);
        }

        if ($membership->role === MembershipRole::Owner && $role !== MembershipRole::Owner) {
            $ownersCount = Membership::query()
                ->where('account_id', $account->id)
                ->where('role', MembershipRole::Owner)
                ->count();

            if ($ownersCount <= 1) {
                throw ValidationException::withMessages([
                    'role' => 'Au moins un dirigeant est requis.',
                ]);
            }
        }

        $membership->update(['role' => $role]);

        return back();
    }

    private function ensureOwner(User $user, Account $account): void
    {
        if (! $user->isAccountOwner($account)) {
            abort(403);
        }
    }
}

