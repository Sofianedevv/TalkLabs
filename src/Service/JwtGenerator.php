<?php
namespace App\Service;

use Firebase\JWT\JWT;

class JwtGenerator
{
    public function generateToken(array $topics): string
    {
        $payload = [
            'mercure' => [
                'publish' => $topics,
                'subscribe' => $topics,
            ],
            'exp' => time() + 86400 ,
        ];

        return JWT::encode(
            $payload,
            'OyTN3Vb+beZeeXBG4O0p7Lb8+XNoEY4JD2oaReh/+NY=',
            'HS256'
        );
    }

    public function generateSuscriberToken(array $topics): string
    {
        $payload = [
            'mercure' => [
                'subscribe' => $topics,
            ],
            'exp' => time() + 86400 ,
        ];

        return JWT::encode(
            $payload,
            $_ENV['MERCURE_SUBSCRIBER_JWT_SECRET'],
            'HS256'
        );
    }
}
