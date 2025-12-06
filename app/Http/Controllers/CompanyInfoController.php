<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CompanyInfoController extends Controller
{
    public function lookup(Request $request): JsonResponse
    {
        try {
            Log::info('CompanyInfo lookup called', ['ip' => $request->ip(), 'input' => $request->all()]);
            $cvr = $request->input('cvr');
            if (!preg_match('/^\d{8}$/', $cvr)) {
                return response()->json(['error' => 'Invalid CVR'], 422);
            }

            // no local mock enabled

            // Try to use worksome/company-info package if bound in container
            if (app()->bound('Worksome\\CompanyInfo\\Client') || class_exists('Worksome\\CompanyInfo\\Client')) {
                try {
                    $client = app()->make('Worksome\\CompanyInfo\\Client');
                    $data = $client->lookup($cvr);
                    return response()->json([
                        'name' => $data['name'] ?? null,
                        'address' => $data['address'] ?? null,
                        'zipcode' => $data['zipcode'] ?? null,
                        'city' => $data['city'] ?? null,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Worksome client lookup failed', ['exception' => $e]);
                }
            }

            // Fallback: try common package helper/function
            if (function_exists('company_info_lookup')) {
                try {
                    $data = company_info_lookup($cvr);
                    return response()->json([
                        'name' => $data['name'] ?? null,
                        'address' => $data['address'] ?? null,
                        'zipcode' => $data['zipcode'] ?? null,
                        'city' => $data['city'] ?? null,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('company_info_lookup helper failed', ['exception' => $e]);
                }
            }

            // Preferred: call configured provider from config/company-info.php
            try {
                $providerKey = config('company-info.active_provider');
                $providers = config('company-info.providers', []);
                if ($providerKey && isset($providers[$providerKey])) {
                    $provider = $providers[$providerKey];
                    $base = rtrim($provider['base_url'] ?? '', '/');
                    $path = $provider['search_path'] ?? '/search';
                    $queryParam = $provider['query_param'] ?? 'search';
                    $url = $base.$path.'?'.$queryParam.'='.urlencode($cvr);

                    $client = \Illuminate\Support\Facades\Http::withHeaders(array_merge([
                        'User-Agent' => 'Evalua/1.0',
                        'Accept' => 'application/json',
                    ], $provider['headers'] ?? []))->timeout(6)->get($url);

                    // If provider is cvrapi and initial request failed, try with country=dk param
                    if (! $client->successful() && ($providerKey === 'cvrapi')) {
                        $url2 = $url.'&country=dk';
                        $client = \Illuminate\Support\Facades\Http::withHeaders(array_merge([
                            'User-Agent' => 'Evalua/1.0',
                            'Accept' => 'application/json',
                        ], $provider['headers'] ?? []))->timeout(6)->get($url2);
                    }

                    // Additional cvrapi fallback: some installations expect query directly on base '/api'
                    if (! $client->successful() && ($providerKey === 'cvrapi')) {
                        $url3 = rtrim($provider['base_url'] ?? '', '/').'?'.($provider['query_param'] ?? 'search').'='.urlencode($cvr).'&country=dk';
                        $client = \Illuminate\Support\Facades\Http::withHeaders(array_merge([
                            'User-Agent' => 'Evalua/1.0',
                            'Accept' => 'application/json',
                        ], $provider['headers'] ?? []))->timeout(6)->get($url3);
                    }

                    if ($client->successful()) {
                        $json = $client->json();
                            // Support provider returning array or single object; pick first non-empty element
                            $first = null;
                            if (is_array($json)) {
                                // if numeric-indexed array, take first element
                                $numericKeys = array_filter(array_keys($json), 'is_int');
                                if (count($numericKeys) && isset($json[0]) && is_array($json[0])) {
                                    $first = $json[0];
                                } elseif (isset($json['vat']) || isset($json['name'])) {
                                    $first = $json;
                                }
                            }
                        if (! $first) {
                            return response()->json(['error' => 'No data from provider'], 502);
                        }

                        return response()->json([
                            'name' => $first['name'] ?? ($first['companyName'] ?? null),
                            'address' => $first['address'] ?? ($first['street'] ?? null),
                            'zipcode' => $first['zipcode'] ?? ($first['postCode'] ?? $first['postcode'] ?? null),
                            'city' => $first['city'] ?? ($first['town'] ?? null),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Configured provider lookup failed', ['exception' => $e, 'provider' => $providerKey ?? null]);
            }

            Log::warning('CompanyInfo lookup attempted but no provider succeeded', ['cvr' => $cvr]);
            return response()->json(['error' => 'Company info provider not available'], 501);
        } catch (\Throwable $e) {
            Log::error('CompanyInfo lookup error', ['exception' => $e, 'input' => $request->all()]);
            return response()->json(['error' => 'Internal error while looking up company info'], 500);
        }
    }
}
