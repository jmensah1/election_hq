<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Services\ResultsService;
use Illuminate\Http\Request;

class PrintElectionResultsController extends Controller
{
    public function __invoke(Request $request, Election $election, ResultsService $resultsService)
    {
        // Permission check
        if (!auth()->user()?->is_super_admin && !auth()->user()->can('manage', $election)) {
            abort(403);
        }

        // Only allow printing if completed or results published, unless super admin
        if (!$election->results_published && $election->status !== 'completed' && !auth()->user()?->is_super_admin) {
             abort(403, 'Results are not yet published.');
        }

        $results = $resultsService->getElectionResults($election);

        return view('admin.elections.print-results', [
            'election' => $election,
            'results' => $results,
        ]);
    }
}
