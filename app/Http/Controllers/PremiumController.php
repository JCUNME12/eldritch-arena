<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PremiumController extends Controller
{
    public function index(): View
    {
        return view('premium.index');
    }

    public function subscribe(Request $request): RedirectResponse
    {
        abort_unless(config('features.demo_premium_subscription'), 404);

        $validated = $request->validate([
            'plan' => ['required', 'in:player_premium,loja_premium'],
        ]);

        $request->user()->update([
            'premium_plan' => $validated['plan'],
            'premium_active' => true,
            'premium_started_at' => Carbon::now(),
        ]);

        return redirect()
            ->route('premium')
            ->with('status', 'Assinatura premium simulada ativada com sucesso para demonstração do TCC.');
    }
}
