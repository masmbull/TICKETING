<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MicrosoftGraphMailService
{
    public function send(string $to, string $subject, string $body, string $contentType = 'Text'): int
    {
        $from = config('services.microsoft-graph.mail_from');

        if (empty($from)) {
            throw new RuntimeException('Missing MS Graph config: services.microsoft-graph.mail_from');
        }

        $token = $this->getAccessToken();

        $payload = [
            'message' => [
                'subject' => $subject,
                'body' => ['contentType' => $contentType, 'content' => $body],
                'toRecipients' => [['emailAddress' => ['address' => $to]]],
            ],
            'saveToSentItems' => true,
        ];

        try {
            // Graph answers 202 Accepted with an empty body on success.
            $response = Http::withToken($token)
                ->acceptJson()
                ->withOptions($this->tlsOptions())
                ->post("https://graph.microsoft.com/v1.0/users/{$from}/sendMail", $payload);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Microsoft Graph unreachable: '.$e->getMessage(), 0, $e);
        }

        if ($response->status() !== 202) {
            throw new RuntimeException(sprintf(
                'Graph sendMail failed (HTTP %d): %s',
                $response->status(),
                $response->json('error.message') ?? $response->body()
            ));
        }

        return $response->status();
    }

    /**
     * TLS options for environments without a system CA store. Verification is
     * never disabled; an explicit bundle is supplied instead.
     */
    private function tlsOptions(): array
    {
        $ca = config('services.microsoft-graph.ca_bundle');

        return $ca ? ['curl' => [CURLOPT_CAINFO => $ca]] : [];
    }

    /**
     * OAuth2 client-credentials flow against the Microsoft identity platform.
     * Returns the raw access token; callers must never log or echo it.
     */
    public function getAccessToken(): string
    {
        $c = config('services.microsoft-graph');

        foreach (['tenant_id', 'client_id', 'client_secret'] as $key) {
            if (empty($c[$key])) {
                throw new RuntimeException("Missing MS Graph config: services.microsoft-graph.{$key}");
            }
        }

        try {
            $response = Http::asForm()
                ->withOptions($this->tlsOptions())
                ->post("https://login.microsoftonline.com/{$c['tenant_id']}/oauth2/v2.0/token", [
                    'grant_type' => 'client_credentials',
                    'client_id' => $c['client_id'],
                    'client_secret' => $c['client_secret'],
                    'scope' => 'https://graph.microsoft.com/.default',
                ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Microsoft identity platform unreachable: '.$e->getMessage(), 0, $e);
        }

        if ($response->failed()) {
            // AAD error_description carries no credentials; safe to surface.
            throw new RuntimeException(sprintf(
                'Token request failed (HTTP %d): %s',
                $response->status(),
                $response->json('error_description') ?? $response->body()
            ));
        }

        $token = $response->json('access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Token response did not contain an access token.');
        }

        return $token;
    }
}
