<?php
/**
 * PhytoKycSandboxClient — Sandbox.co.in API wrapper for PAN and GST verification.
 *
 * Sandbox.co.in uses a two-step auth model:
 *  1. POST /authenticate with the raw API key → returns a short-lived access_token
 *  2. All subsequent calls use Authorization: Bearer {access_token}
 *
 * The token is cached in PS Configuration for 55 minutes to avoid re-authenticating
 * on every request.
 */
if (!defined('_PS_VERSION_')) { exit; }

class PhytoKycSandboxClient
{
    const BASE_URL     = 'https://api.sandbox.co.in';
    const API_VERSION  = '2.0';
    const TOKEN_KEY    = 'PHYTO_KYC_SANDBOX_TOKEN';
    const EXPIRY_KEY   = 'PHYTO_KYC_SANDBOX_TOKEN_EXPIRY';

    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    // ── Public methods ────────────────────────────────────────────────────────

    /**
     * Verify a PAN number.
     * Returns ['valid' => bool, 'name' => string, 'raw' => array, 'error' => string|null]
     */
    public function verifyPan(string $pan): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['valid' => false, 'name' => '', 'raw' => [], 'error' => 'Could not authenticate with Sandbox API.'];
        }

        $raw = $this->request('POST', '/kyc/pan/verify', ['pan' => strtoupper(trim($pan))], [
            'Authorization' => 'Bearer ' . $token,
            'x-api-version' => self::API_VERSION,
        ]);

        if (isset($raw['error'])) {
            return ['valid' => false, 'name' => '', 'raw' => $raw, 'error' => $raw['error']];
        }

        $status = $raw['data']['status'] ?? $raw['status'] ?? '';
        $valid  = in_array(strtoupper($status), ['VALID', 'EXISTING'], true);
        $name   = $raw['data']['name'] ?? $raw['data']['full_name'] ?? '';

        return ['valid' => $valid, 'name' => (string) $name, 'raw' => $raw, 'error' => null];
    }

    /**
     * Verify a GST number (GSTIN).
     * Returns ['valid' => bool, 'business_name' => string, 'raw' => array, 'error' => string|null]
     */
    public function verifyGst(string $gstin): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['valid' => false, 'business_name' => '', 'raw' => [], 'error' => 'Could not authenticate with Sandbox API.'];
        }

        $raw = $this->request('GET', '/gst/taxpayer/' . urlencode(strtoupper(trim($gstin))), [], [
            'Authorization' => 'Bearer ' . $token,
            'x-api-version' => self::API_VERSION,
        ]);

        if (isset($raw['error'])) {
            return ['valid' => false, 'business_name' => '', 'raw' => $raw, 'error' => $raw['error']];
        }

        $status       = $raw['data']['status'] ?? $raw['data']['sts'] ?? '';
        $valid         = strtolower($status) === 'active';
        $businessName  = $raw['data']['tradeNam'] ?? $raw['data']['lgnm'] ?? '';

        return ['valid' => $valid, 'business_name' => (string) $businessName, 'raw' => $raw, 'error' => null];
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    private function getAccessToken(): string
    {
        $expiry = (int) Configuration::get(self::EXPIRY_KEY);
        $token  = (string) Configuration::get(self::TOKEN_KEY);

        if ($token && $expiry > time()) {
            return $token;
        }

        // Exchange API key for access token
        $raw = $this->request('POST', '/authenticate', [], ['Authorization' => $this->apiKey]);
        $token = $raw['access_token'] ?? '';

        if ($token) {
            // Sandbox tokens last ~60 min; cache for 55 min
            Configuration::updateValue(self::TOKEN_KEY,   $token);
            Configuration::updateValue(self::EXPIRY_KEY,  time() + 3300);
        }

        return $token;
    }

    // ── HTTP ──────────────────────────────────────────────────────────────────

    private function request(string $method, string $path, array $body, array $headers): array
    {
        $curlHeaders = ['Content-Type: application/json', 'Accept: application/json'];
        foreach ($headers as $k => $v) {
            $curlHeaders[] = $k . ': ' . $v;
        }

        $ch = curl_init(self::BASE_URL . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => $curlHeaders,
            CURLOPT_CUSTOMREQUEST  => $method,
        ]);

        if ($method === 'POST' && !empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $result     = curl_exec($ch);
        $curlError  = curl_error($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            return ['error' => 'cURL error: ' . $curlError];
        }

        $decoded = json_decode($result, true);
        if (!is_array($decoded)) {
            return ['error' => 'Invalid JSON response (HTTP ' . $httpStatus . ')'];
        }

        return $decoded;
    }
}
