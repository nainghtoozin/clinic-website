<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->safe()->only(['name', 'email', 'phone']);

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar')->store('avatars', 'public');

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $avatar;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if (url()->previous() === route('user.settings')) {
            return Redirect::route('user.settings')->with('status', 'profile-updated');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Deactivate the user's account.
     *
     * Accounts are deactivated (soft-closed) instead of hard-deleted because
     * clinical records (doctor profiles, appointments, prescriptions) and
     * billing audit history must be preserved.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->wouldRemoveLastAdministrator()) {
            return back()->withErrors([
                'password' => __('app.account.delete_prevented_last_admin'),
            ], 'userDeletion');
        }

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
            'confirm_email' => ['required', 'string', 'email'],
        ]);

        if (strcasecmp((string) $request->input('confirm_email'), (string) $user->email) !== 0) {
            return back()->withErrors([
                'confirm_email' => __('app.account.confirm_email_mismatch'),
            ], 'userDeletion');
        }

        $user->forceFill(['is_active' => false])->save();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('login')->with('status', __('app.account.deactivated_success'));
    }
}