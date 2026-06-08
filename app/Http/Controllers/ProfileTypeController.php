<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileTypeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $request->validate(['type' => ['required', 'in:player,organizer']]);
        $request->user()->update(['type' => $request->type]);
        return redirect()->route('dashboard')->with('status', 'Perfil alterado com sucesso.');
    }
}
