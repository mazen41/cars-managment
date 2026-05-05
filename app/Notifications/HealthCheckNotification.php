<?php

namespace App\Notifications;

use App\Health\AdminNotifiable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use Spatie\Health\ResultStores\ResultStore;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class HealthCheckNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $checkResults;

    public function __construct($checkResults)
    {
        $this->checkResults = $checkResults;
    }


    protected function getFailedChecks(): Collection
    {
        return collect($this->checkResults)->filter(function ($result) {
            return $result->status->value === 'failed';
        });
    }

    public function via($notifiable)
    {
      return [
        'mail',
        WebPushChannel::class,
      ];
    }

    public function shouldSend($notifiable, string $channel): bool
    {
        if (! config('health.notifications.enabled')) {
            return false;
        }

        /** @var int $throttleMinutes */
        $throttleMinutes = config('health.notifications.throttle_notifications_for_minutes');

        if ($throttleMinutes === 0) {
            return true;
        }

        $cacheKey = config('health.notifications.throttle_notifications_key', 'health:latestNotificationSentAt:').$channel;

        /** @var \Illuminate\Cache\CacheManager $cache */
        $cache = app('cache');

        /** @var string $timestamp */
        $timestamp = $cache->get($cacheKey);

        if (! $timestamp) {
            $cache->set($cacheKey, now()->timestamp);

            return true;
        }

        if (Carbon::createFromTimestamp($timestamp)->addMinutes($throttleMinutes)->isFuture()) {
            return false;
        }

        $cache->set($cacheKey, now()->timestamp);

        return true;
    }


    public function toMail($notifiable)
    {
        $failedChecks = $this->getFailedChecks();

        $mailMessage = (new MailMessage)
            ->error()
            ->subject('Health Check Failed')
            ->greeting('Hello!')
            ->line('The following health checks have failed:');

        foreach ($failedChecks as $check) {
            $checkName = $check->check->name ?? class_basename($check->check);
            $message = $check->notificationMessage;

            // Replace placeholders in message if they exist
            if (isset($check->meta['expected'], $check->meta['actual'])) {
                $message = strtr($message, [
                    ':expected' => $check->meta['expected'],
                    ':actual' => $check->meta['actual']
                ]);
            }

            $mailMessage->line("⚠️ {$checkName}: {$message}");
        }

        $mailMessage->line('Please check your application\'s health dashboard for more details.');

        return $mailMessage;
    }

    public function toWebPush($notifiable, $notification)
    {
        $failedChecks = $this->getFailedChecks();
        $failedCount = $failedChecks->count();

        // Create a formatted list of failed checks with their messages
        $failedCheckDetails = $failedChecks->map(function ($check) {
            $name = $this->getCheckName($check);
            $message = $this->formatCheckMessage($check);
            return "• {$name}: {$message}";
        })->implode("\n");

        return (new WebPushMessage)
            ->title($failedCount . ' Health Check(s) Failed')
            ->icon(static_asset('assets/img/app_logo.png'))
            ->body($failedCheckDetails)
            ->tag('health-check')
            ->data([
                'url' => route('health.index'),
                'failedChecks' => $failedChecks->map(function ($check) {
                    return [
                        'name' => $this->getCheckName($check),
                        'message' => $this->formatCheckMessage($check)
                    ];
                })->toArray()
            ])
            ->options(['TTL' => 1000]);
    }

    protected function formatCheckMessage($check): string
    {
        $message = $check->notificationMessage;

        if (isset($check->meta['expected'], $check->meta['actual'])) {
            $message = strtr($message, [
                ':expected' => $check->meta['expected'],
                ':actual' => $check->meta['actual']
            ]);
        }


        $message = str_replace('`', '', $message); // Remove backticks
        return $message;
    }

    protected function getCheckName($check)
    {
        if (!empty($check->check->name)) {
            return $check->check->name;
        }

        $className = class_basename($check->check);


        $mappings = [
            'DebugModeCheck' => 'Debug Mode',
            'EnvironmentCheck' => 'Environment',
            'UsedDiskSpaceCheck' => 'Disk Space',
            'DatabaseCheck' => 'Database',

        ];

        if (isset($mappings[$className])) {
            return $mappings[$className];
        }


        $name = str_replace('Check', '', $className);
        return trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $name));
    }
}
