<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

class GitUpdateController extends Controller
{
    /**
     * Get Git repository info and status.
     */
    public function info(): JsonResponse
    {
        $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD 2>&1') ?? 'unknown');
        $commit = trim(shell_exec('git rev-parse --short HEAD 2>&1') ?? 'unknown');
        $lastCommitMsg = trim(shell_exec('git log -1 --pretty=%B 2>&1') ?? 'unknown');
        $status = trim(shell_exec('git status -s 2>&1') ?? '');

        return response()->json([
            'branch' => $branch,
            'commit' => $commit,
            'last_commit' => $lastCommitMsg,
            'status' => $status ?: 'Working tree clean',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ]);
    }

    /**
     * Pull origin code, execute migrations and flush caches.
     */
    public function update(Request $request): JsonResponse
    {
        $output = [];

        // 1. Git pull
        $gitPull = shell_exec('git pull 2>&1');
        $output[] = "=== Git Pull ===\n" . ($gitPull ?: 'No output');

        // 2. Migrate
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output[] = "=== Database Migrate ===\n" . Artisan::output();
        } catch (\Throwable $e) {
            $output[] = "=== Migrate Error ===\n" . $e->getMessage();
        }

        // 3. Optimize / Clear Cache
        try {
            Artisan::call('optimize:clear');
            $output[] = "=== Cache Clear ===\n" . Artisan::output();
        } catch (\Throwable $e) {
            $output[] = "=== Cache Error ===\n" . $e->getMessage();
        }

        return response()->json([
            'success' => true,
            'message' => 'Update finished',
            'output' => implode("\n\n", $output),
        ]);
    }
}
