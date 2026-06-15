<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\WebPushFcmOptions;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private $messaging;

    public function __construct()
    {
        try {
            $credentialsPath = config('firebase.credentials.file');

            if (!file_exists($credentialsPath)) {
                Log::warning('Firebase credentials file not found', ['path' => $credentialsPath]);
                return;
            }

            $this->messaging = (new Factory)
                ->withServiceAccount($credentialsPath)
                ->createMessaging();
        } catch (\Throwable $e) {
            Log::error('Firebase init error: ' . $e->getMessage());
        }
    }

    public function isConfigured(): bool
    {
        return $this->messaging !== null;
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = [], ?string $clickUrl = null): array
    {
        if (!$this->isConfigured() || empty($tokens)) {
            return ['success' => 0, 'failures' => count($tokens)];
        }

        $notification = FcmNotification::create($title, $body);

        $webPushConfig = null;
        if ($clickUrl) {
            $webPushConfig = WebPushConfig::fromArray([
                'fcm_options' => [
                    'link' => $clickUrl,
                ],
            ]);
        }

        $messages = [];
        foreach ($tokens as $token) {
            $payload = array_map('strval', $data);
        if ($clickUrl) {
            $payload['click_action'] = $clickUrl;
        }

        $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($payload);

            if ($webPushConfig) {
                $message = $message->withWebPushConfig($webPushConfig);
            }

            $messages[] = $message;
        }

        try {
            $report = $this->messaging->sendAll($messages);
            return [
                'success' => $report->successes()->count(),
                'failures' => $report->failures()->count(),
            ];
        } catch (\Throwable $e) {
            Log::error('FCM sendToTokens error: ' . $e->getMessage());
            return ['success' => 0, 'failures' => count($tokens)];
        }
    }

    public function sendToTopic(string $topic, string $title, string $body, array $data = [], ?string $clickUrl = null): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $notification = FcmNotification::create($title, $body);

            $payload = array_map('strval', $data);
            if ($clickUrl) {
                $payload['click_action'] = $clickUrl;
            }

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification)
                ->withData($payload);

            if ($clickUrl) {
                $message = $message->withWebPushConfig(
                    WebPushConfig::fromArray([
                        'fcm_options' => [
                            'link' => $clickUrl,
                        ],
                    ])
                );
            }

            $this->messaging->send($message);
            return true;
        } catch (\Throwable $e) {
            Log::error('FCM sendToTopic error: ' . $e->getMessage());
            return false;
        }
    }

    public function subscribeToTopic(string $topic, array $tokens): bool
    {
        if (!$this->isConfigured() || empty($tokens)) {
            return false;
        }

        try {
            $this->messaging->subscribeToTopic($topic, $tokens);
            return true;
        } catch (\Throwable $e) {
            Log::error('FCM subscribeToTopic error: ' . $e->getMessage());
            return false;
        }
    }

    public function unsubscribeFromTopic(string $topic, array $tokens): bool
    {
        if (!$this->isConfigured() || empty($tokens)) {
            return false;
        }

        try {
            $this->messaging->unsubscribeFromTopic($topic, $tokens);
            return true;
        } catch (\Throwable $e) {
            Log::error('FCM unsubscribeFromTopic error: ' . $e->getMessage());
            return false;
        }
    }
}
