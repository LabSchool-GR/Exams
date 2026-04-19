<?php

namespace App\Http\Controllers;

use App\Services\ApplicationUpdateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SystemUpdateController extends Controller
{
    /**
     * Display the read-only update center for administrators.
     */
    public function index(Request $request, ApplicationUpdateService $updateService): View
    {
        if ($request->boolean('refresh')) {
            $updateService->forgetCachedRelease();
        }

        $updateStatus = $updateService->getStatus();

        return view('system_updates.index', compact('updateStatus'));
    }
}
