<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class RoutesStatusChecked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $totalRoutes;

    public int $successful;

    public int $failed;

    public int $errors;

    public string $environment;

    public float $executionTime;

    /**
     * Create a new event instance.
     */
    public function __construct(int $totalRoutes, int $successful, int $failed, int $errors, string $environment, float $executionTime)
    {
        $this->totalRoutes = $totalRoutes;
        $this->successful = $successful;
        $this->failed = $failed;
        $this->errors = $errors;
        $this->environment = $environment;
        $this->executionTime = $executionTime;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('sitemap'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $successRate = $this->totalRoutes > 0 ? round(($this->successful / $this->totalRoutes) * 100, 2) : 0;

        return [
            'total_routes' => $this->totalRoutes,
            'successful' => $this->successful,
            'failed' => $this->failed,
            'errors' => $this->errors,
            'success_rate' => $successRate,
            'environment' => $this->environment,
            'execution_time' => $this->executionTime,
            'timestamp' => now()->toISOString(),
        ];
    }
}
