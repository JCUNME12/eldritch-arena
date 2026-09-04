<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(): View
    {
        return view('account.edit');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'type' => ['required', Rule::in(['player', 'organizer'])],
        ]);

        $emailChanged = $validated['email'] !== $user->email;
        $user->fill($validated);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('status', 'Dados da conta atualizados.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'current_password.current_password' => 'A senha atual não confere.',
        ]);

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return back()->with('status', 'Senha atualizada com sucesso.');
    }
}
