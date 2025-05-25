<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class DeployController extends Controller
{
    public function deploy(Request $request)
    {
        $githubPayload = $request->getContent();
        $githubHash = $request->header('X-Hub-Signature-256');
        $localToken = config('app.deploy_secret');
        $localHash = 'sha256='.hash_hmac('sha256', $githubPayload, $localToken, false);
        if (hash_equals($githubHash, $localHash)) {
            $root_path = base_path();

            // refactored example to use Process available in Laravel v10.
            // but we're still using v9.

            // $result = Process::path(__DIR__)
            //     ->timeout(10 * 60)
            //     ->run('./deploy.sh', function (string $type, string $output) {
            //         echo $output;
            //     });

            $process = new Process(['./deploy.sh']);
            $process->setWorkingDirectory(base_path());
            $process->run(function ($type, $buffer) {
                echo $buffer;
                Log::info($buffer);
            });
        }
    }
}
