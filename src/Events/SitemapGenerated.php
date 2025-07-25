<?php

namespace LaravelPlus\Sitemap\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SitemapGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $format;
    public int $fileSize;
    public float $executionTime;
    public string $environment;
    public ?string $filePath;

    /**
     * Create a new event instance.
     */
    public function __construct(string $format, int $fileSize, float $executionTime, string $environment, ?string $filePath = null)
    {
        $this->format = $format;
        $this->fileSize = $fileSize;
        $this->executionTime = $executionTime;
        $this->environment = $environment;
        $this->filePath = $filePath;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
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
            'format' => $this->format,
            'file_size' => $this->formatFileSize($this->fileSize),
            'file_size_bytes' => $this->fileSize,
            'execution_time' => $this->executionTime,
            'environment' => $this->environment,
            'file_path' => $this->filePath,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Format file size for display.
     */
    protected function formatFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' KB';
        } else {
            return round($bytes / (1024 * 1024), 1) . ' MB';
        }
    }
} 