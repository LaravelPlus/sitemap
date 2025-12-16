<?php

declare(strict_types=1);

namespace LaravelPlus\Sitemap\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class RoutesDiscovered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $routesDiscovered;

    public int $routesStored;

    public string $environment;

    public float $executionTime;

    /**
     * Create a new event instance.
     */
    public function __construct(int $routesDiscovered, int $routesStored, string $environment, float $executionTime)
    {
        $this->routesDiscovered = $routesDiscovered;
        $this->routesStored = $routesStored;
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
        return [
            'routes_discovered' => $this->routesDiscovered,
            'routes_stored' => $this->routesStored,
            'environment' => $this->environment,
            'execution_time' => $this->executionTime,
            'timestamp' => now()->toISOString(),
        ];
    }
}
