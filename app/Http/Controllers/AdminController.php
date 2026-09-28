<?php

namespace App\Http\Controllers;

use App\Models\CardListing;
use App\Models\Store;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __invoke(Request $r)
    {
        $filters = $r->validate(['q' => ['nullable', 'string', 'max:100']]);
        $users = User::query()->when($filters['q'] ?? null, fn ($q, $v) => $q->where(fn ($s) => $s->whereLike('name', '%'.$v.'%')->orWhereLike('email', '%'.$v.'%')))->latest()->paginate(20)->withQueryString();

        return view('admin.index', ['users' => $users, 'counts' => ['Contas' => User::count(), 'Lojas' => Store::count(), 'Anúncios disponíveis' => CardListing::available()->count(), 'Torneios' => Tournament::count()]]);
    }
}
