<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Katalog komponen x-sg.* untuk QA visual redesign (light/dark, 3 breakpoint).
 * Hanya admin, atau siapa saja di environment local.
 */
class UiKitController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless(app()->isLocal() || $request->user()->isAdmin(), 404);

        return view('ui-kit', ['user' => $request->user()]);
    }
}
