<?php

namespace App\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class ReverbCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();


        $host = config('reverb.servers.default.host') ?? env('REVERB_HOST', '0.0.0.0');
        $port = config('reverb.servers.default.port') ?? env('REVERB_SERVER_PORT', 8080);

        $socket = @fsockopen($host, $port, $errno, $errstr, 2); // 2-second timeout

        if ($socket) {
            $result->ok()->shortSummary("Running on {$host}:{$port}");
            fclose($socket);
        } else {
            $result->failed("Reverb server is not reachable on {$host}:{$port}.");
        }

        return $result;
    }
}
