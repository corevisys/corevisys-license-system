<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::with('owner')->get()->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'owner_name' => $t->owner->name,
            'member_count' => $t->users()->count(),
            'active_licenses' => $t->licenses()->where('status', 'active')->count(),
        ]);

        return Inertia::render('Admin/Teams', ['teams' => $teams]);
    }
}