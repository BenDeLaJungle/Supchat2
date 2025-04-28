<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class SubscribeController extends AbstractController
{
    private string $mercureUrl;
    private string $jwtSecret;

    public function __construct()
    {
        $this->mercureUrl = $_ENV['MERCURE_PUBLIC_URL'] ?? 'http://localhost:3000/.well-known/mercure';
        $this->jwtSecret = $_ENV['MERCURE_JWT_SECRET'] ?? 'changeme';
    }

    #[Route('/subscribe', name: 'subscribe', methods: ['GET'])]
    public function subscribe(Request $request): StreamedResponse
    {
        $topic = $request->query->get('topic');

        if (!$topic) {
            return new StreamedResponse(function () {
                echo 'Missing topic';
            }, 400);
        }

        $mercureUrl = $this->mercureUrl . '?topic=' . urlencode($topic);

        $opts = [
            'http' => [
                'method' => "GET",
                'header' => "Authorization: Bearer " . $this->generateJwt() . "\r\n" .
                            "Accept: text/event-stream\r\n" .
                            "Connection: keep-alive\r\n",
                'ignore_errors' => true,
            ]
        ];

        $context = stream_context_create($opts);
        $stream = fopen($mercureUrl, 'r', false, $context);

        if (!$stream) {
            return new StreamedResponse(function () {
                echo 'Cannot connect to Mercure hub.';
            }, 502);
        }

        return new StreamedResponse(function () use ($stream) {
            while (!feof($stream)) {
                echo fread($stream, 1024);
                @ob_flush();
                flush();
            }
            fclose($stream);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }

    private function generateJwt(): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'mercure' => [
                'subscribe' => ['*']  // Pour l’instant open-bar
            ],
            'exp' => time() + 3600, // 1 heure de validité
        ];

        $base64UrlHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $base64UrlPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->jwtSecret, true);
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }
}
