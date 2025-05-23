<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;k

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

            // refactored example to use Process available in Laravel v10.
            // but we're still using v9.

            // $result = Process::path(__DIR__)
            //     ->timeout(10 * 60)
            //     ->run('./deploy.sh', function (string $type, string $output) {
            //         echo $output;
            //     });

            $process = new Process('cd '.$root_path.'; ./deploy.sh');
            $process->run(function ($type, $buffer) {
                echo $buffer;
            });
        }
    }
}
