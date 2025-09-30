<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CamelCase
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Zet input keys om naar snake_case
        $request->replace($this->convertKeysToSnakeCase($request->all()));

        return $next($request);
    }

    private function convertKeysToSnakeCase($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $newKey = is_string($key) ? Str::snake($key) : $key;
                $result[$newKey] = $this->convertKeysToSnakeCase($value);
            }
            return $result;
        }
        return $data;
    }
}
