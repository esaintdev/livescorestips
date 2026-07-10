<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;
    protected $channelId;
    protected $enabled;
    protected $baseUrl;

    public function __construct()
    {
        $this->botToken = Setting::get('telegram_bot_token') ?? config('services.telegram.bot_token');
        $this->channelId = Setting::get('telegram_channel_id') ?? config('services.telegram.channel_id');
        $this->enabled = Setting::get('telegram_enabled') == '1';
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Send a prediction to the Telegram channel
     */
    public function sendPrediction($prediction)
    {
        Log::info('TelegramService::sendPrediction called', [
            'enabled' => $this->enabled,
            'has_token' => !empty($this->botToken),
            'has_channel' => !empty($this->channelId),
            'prediction_id' => $prediction->id ?? 'unknown',
            'category' => $prediction->category ?? 'none'
        ]);

        if (!$this->enabled || !$this->botToken || !$this->channelId) {
            Log::warning('Telegram notification skipped', [
                'enabled' => $this->enabled,
                'has_token' => !empty($this->botToken),
                'has_channel' => !empty($this->channelId)
            ]);
            return false;
        }

        // Category filter - only send specific categories
        $allowedCategories = $this->getAllowedCategories();

        if (!empty($allowedCategories)) {
            $predictionCategory = strtolower($prediction->category ?? '');
            $isAllowed = false;

            foreach ($allowedCategories as $category) {
                if (strtolower($category) === $predictionCategory) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                Log::info('Telegram notification skipped - category not allowed', [
                    'prediction_category' => $prediction->category,
                    'allowed_categories' => $allowedCategories
                ]);
                return false;
            }
        }

        try {
            $message = $this->formatPredictionMessage($prediction);
            Log::info('Telegram message formatted', ['message_length' => strlen($message)]);

            $result = $this->sendMessage($message);
            Log::info('Telegram send result', ['success' => $result]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Telegram send prediction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get allowed categories for Telegram notifications
     * Empty array means all categories are allowed
     */
    protected function getAllowedCategories()
    {
        $categoriesString = Setting::get('telegram_categories', '');

        if (empty($categoriesString)) {
            return []; // Empty = all categories allowed
        }

        // Split by comma and trim whitespace
        return array_map('trim', explode(',', $categoriesString));
    }

    /**
     * Format prediction as Telegram message
     */
    protected function formatPredictionMessage($prediction)
    {
        $emoji = [
            'ball' => '⚽',
            'trophy' => '🏆',
            'home' => '🏟️',
            'clock' => '⏰',
            'bulb' => '💡',
            'chart' => '📊',
            'target' => '🎯',
            'link' => '🔗',
            'fire' => '🔥',
            'check' => '✅',
        ];

        $status = '';
        if ($prediction->status === 'won') {
            $status = "\n{$emoji['check']} Status: WON";
            if ($prediction->home_score !== null && $prediction->away_score !== null) {
                $status .= " ({$prediction->home_score}-{$prediction->away_score})";
            }
        } elseif ($prediction->status === 'lost') {
            $status = "\n❌ Status: LOST";
            if ($prediction->home_score !== null && $prediction->away_score !== null) {
                $status .= " ({$prediction->home_score}-{$prediction->away_score})";
            }
        }

        // Use slug if available, otherwise fixture_id
        $matchUrl = route('fixture.show', [$prediction->slug ?? $prediction->fixture_id, $prediction->match_date->format('Y-m-d')]);

        // Format match time
        $matchTime = $prediction->match_time;
        if ($prediction->match_date) {
            $matchTime = \Carbon\Carbon::parse($prediction->match_date)->format('M d') . ' at ' . $matchTime;
        }

        $message = "{$emoji['fire']} NEW PREDICTION {$emoji['fire']}\n\n";
        $message .= "{$emoji['trophy']} " . ($prediction->league_name ?? 'League') . "\n";
        $message .= "{$emoji['home']} {$prediction->home_team} vs {$prediction->away_team}\n\n";
        $message .= "{$emoji['clock']} Time: {$matchTime}\n";
        $message .= "{$emoji['bulb']} Tip: {$prediction->tip}\n";
        $message .= "{$emoji['chart']} Odds: {$prediction->odds}\n";
        $message .= "{$emoji['target']} Probability: {$prediction->probability}%";
        $message .= $status;
        $message .= "\n\n{$emoji['link']} View Details: {$matchUrl}";

        return $message;
    }

    /**
     * Send a message to the Telegram channel
     */
    public function sendMessage($message, $parseMode = null)
    {
        if (!$this->botToken || !$this->channelId) {
            throw new \Exception('Telegram bot token or channel ID not configured');
        }

        try {
            $params = [
                'chat_id' => $this->channelId,
                'text' => $message,
                'disable_web_page_preview' => false,
            ];

            if ($parseMode) {
                $params['parse_mode'] = $parseMode;
            }

            Log::info('Sending Telegram message', [
                'channel_id' => $this->channelId,
                'message_preview' => substr($message, 0, 100)
            ]);

            $response = Http::post("{$this->baseUrl}/sendMessage", $params);

            Log::info('Telegram API response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                Log::info('Telegram message sent successfully');
                return true;
            }

            $error = $response->json();
            Log::error('Telegram API error', ['error' => $error]);
            return false;
        } catch (\Exception $e) {
            Log::error('Telegram send message exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Test the Telegram connection
     */
    public function testConnection()
    {
        if (!$this->botToken) {
            return [
                'success' => false,
                'message' => 'Bot token is not configured'
            ];
        }

        try {
            // Test bot token by getting bot info
            $response = Http::get("{$this->baseUrl}/getMe");

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Invalid bot token'
                ];
            }

            $botInfo = $response->json();

            // Test channel access by sending a test message
            if ($this->channelId) {
                $testMessage = "🤖 Test Connection\n\nTelegram bot is connected successfully!";
                $sendResult = $this->sendMessage($testMessage);

                if ($sendResult) {
                    return [
                        'success' => true,
                        'message' => "Connected successfully! Bot: @{$botInfo['result']['username']}"
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Bot token is valid, but cannot send to channel. Make sure bot is admin in the channel.'
                    ];
                }
            }

            return [
                'success' => true,
                'message' => "Bot token is valid! Bot: @{$botInfo['result']['username']}"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get bot information
     */
    public function getBotInfo()
    {
        try {
            $response = Http::get("{$this->baseUrl}/getMe");

            if ($response->successful()) {
                return $response->json()['result'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get bot info: ' . $e->getMessage());
            return null;
        }
    }
}
