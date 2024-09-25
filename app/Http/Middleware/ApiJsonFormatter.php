<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ApiJsonFormatter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $code = $response->status();
            $status = $response->statusText();
            $originalData = $response->getData(true);

            $formattedData = $originalData;
            if (in_array($code, [200, 201]) && !array_key_exists('data', $originalData)) {
                $containMessage = array_key_exists('message', $originalData);
                $containError = array_key_exists('error', $originalData) || array_key_exists('errors', $originalData);
                if (!$containMessage && !$containError) {
                    $formattedData = ['data' => $originalData];
                }
            }
            if ($code >= 400 && array_key_exists('message', $originalData) && !array_key_exists('errors', $originalData)) {
                $formattedData = [
                    'error' => $originalData['message'],
                ];
            }
            if (!App::isProduction() && array_key_exists('exception', $originalData)) {
                $formattedData['meta'] = $originalData;
            }

            $responseData = Collection::make([
                'code' => $code,
                'status' => $status,
            ]);
            $responseData = $responseData->merge($formattedData);
            $response->setData($responseData);
        }

        return $response;
    }
}
