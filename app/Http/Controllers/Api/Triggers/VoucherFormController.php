<?php

namespace App\Http\Controllers\Api\Triggers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VoucherFormController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $all = $request->all();
        $phone = data_get($all, 'customData.phone');
        $email = data_get($all, 'customData.email');
        $name = data_get($all, 'customData.name');
        $workflow = data_get($all, 'workflow.name');

        // Perform the HTTP request using Guzzle
        $response = Http::asForm()->post(env('GOOGLE_FORM_URL'), [
            'entry.2135131916' => $name,
            'entry.715138392' => $phone,
            'entry.1706893644' => $email,
            'entry.1016933667' => $workflow,
            'entry.1297538632' => now()->toDateTimeString(),
        ]);

        $outcome = $response->successful();

        logger($request->all());
        logger(compact('name', 'phone', 'email', 'name', 'workflow', 'outcome'));
        return response()->json(['outcome' => 'success']);
    }
}
