<?php

namespace App\Http\Controllers;

use App\Models\ReconcileResult;
use Illuminate\Http\Request;

class DisbursementController extends Controller
{
    public function index()
    {
        $token_applicant = request()->query('token');
        $status = request()->query('status');

        $query1 = ReconcileResult::query();
        $query2 = ReconcileResult::query();
        $query3 = ReconcileResult::query();
        $query4 = ReconcileResult::query();
        $query5 = ReconcileResult::query();
        $query6 = ReconcileResult::query();

        if ($token_applicant) {
            $query1->where('token_applicant', $token_applicant);
            $query2->where('token_applicant', $token_applicant);
            $query3->where('token_applicant', $token_applicant);
            $query4->where('token_applicant', $token_applicant);
            $query5->where('token_applicant', $token_applicant);
            $query6->where('token_applicant', $token_applicant);
        }

        $match = $query1->where('status', 'MATCH')->count();
        $dispute = $query2->whereIn('status', ['NOT_MATCH', 'NOT_FOUND'])->count();
        $onHold = $query3->where('status', 'NOT_FOUND')->count();

        $sumMatch = $query4->where('status', 'MATCH')->sum('total_sales');
        $sumDispute = $query5->whereIn('status', ['NOT_MATCH', 'NOT_FOUND'])->sum('total_sales');
        $sumHold = $query6->where('status', 'NOT_FOUND')->sum('total_sales');

        return view('modules.reconcile.show', compact('match', 'dispute', 'onHold', 'sumMatch', 'sumDispute', 'sumHold'));
    }
}