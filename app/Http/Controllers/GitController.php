<?php

namespace App\Http\Controllers;

class GitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function pull()
    {
        $base = base_path();
        $php  = PHP_BINARY;

        $phpBin = escapeshellarg($php);

        $steps = [
            ['label' => 'git fetch',        'cmd' => 'git fetch origin'],
            ['label' => 'git reset',        'cmd' => 'git reset --hard origin/main'],
            ['label' => 'composer install', 'cmd' => 'composer install --no-dev --optimize-autoloader --no-interaction'],
            ['label' => 'artisan migrate',  'cmd' => "$phpBin artisan migrate --force --no-interaction"],
        ];

        $results = [];
        $abort   = false;

        foreach ($steps as $step) {
            if ($abort) {
                $results[] = ['label' => $step['label'], 'output' => 'Skipped.', 'success' => false];
                continue;
            }

            $pipes = [];
            $proc  = proc_open(
                $step['cmd'],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
                $base
            );

            $out  = stream_get_contents($pipes[1]);
            $err  = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $code = proc_close($proc);

            $output = trim($out . ($err ? "\n" . $err : ''));
            $success = $code === 0;

            $results[] = ['label' => $step['label'], 'output' => $output, 'success' => $success];

            if (!$success) {
                $abort = true;
            }
        }

        return redirect()->route('home')->with('pull_results', $results);
    }
}
