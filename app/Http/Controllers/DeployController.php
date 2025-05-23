<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class DeployController extends Controller
{
    public function deploy(Request $request)
    {
        $githubPayload = $request->getContent();
        $githubHash = $request->header('X-Hub-Signature');
        $localToken = config('app.deploy_secret');
        $localHash = 'sha1='.hash_hmac('sha1', $githubPayload, $localToken, false);
        if (hash_equals($githubHash, $localHash)) {
            $root_path = base_path();
            $result = Process::path(__DIR__)
                ->timeout(10 * 60)
                ->run('./deploy.sh', function (string $type, string $output) {
                    echo $output;
                });
        }
    }
}
