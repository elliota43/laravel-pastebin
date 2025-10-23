<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Pastebin extends Model
{
    protected $table = "pastebins";

    protected $fillable = [
        "title",
        "content",
        "language",
        "expires_at",
        "visibility",
        "hash"
    ];

    public function generateHash(): string
    {
        $bytes = random_bytes(ceil(16 / 2.0));
        $hashString = bin2hex($bytes);

        return substr($hashString, 0, 16);
    }

    public function size()
    {
        $bytes = strlen($this->content);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 1) . ' ' . $units[$pow];
    }

    /**
     * Converts datetime format to readable human time
     * @param string $time
     * @return string
     */
    public function getCreatedAt()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function getExpiresAt()
    {
        return Carbon::createFromTimestamp($this->expires_at)->diffForHumans();
    }

    public function getContent()
    {
        return html_entity_decode($this->content);
    }
}