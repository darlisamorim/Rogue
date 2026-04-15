<?php

namespace App\Http\Controllers;

use App\Models\PricingConfig;
use App\Models\Resume;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $recentResumes = Resume::with('template')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn ($r) => [
                'id'           => $r->id,
                'title'        => $r->title,
                'template'     => $r->template ? ['name' => $r->template->name] : null,
                'is_downloaded' => $r->is_downloaded,
                'updated_at'   => $r->updated_at->diffForHumans(),
            ]);

        $totalResumes    = Resume::where('user_id', $user->id)->count();
        $totalDownloads  = Resume::where('user_id', $user->id)->where('is_downloaded', true)->count();
        $pendingPayments = Transaction::where('user_id', $user->id)->where('status', 'pending')->count();

        $pricing = PricingConfig::active()
            ->get(['action_slug', 'label', 'price'])
            ->keyBy('action_slug');

        return Inertia::render('Dashboard', [
            'recentResumes'  => $recentResumes,
            'totalResumes'   => $totalResumes,
            'totalDownloads' => $totalDownloads,
            'pendingPayments' => $pendingPayments,
            'pricing'        => $pricing,
        ]);
    }
}
