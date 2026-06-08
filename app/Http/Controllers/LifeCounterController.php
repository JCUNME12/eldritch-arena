<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LifeCounterController extends Controller
{
    public function __invoke(): View
    {
        return view('life-counter.index');
    }
}
