<?php
namespace App\Service;

use App\Utils\GetUniqueId;
use App\Redis\RedisClient;
use App\Utils\SessionData;
use Symfony\Component\HttpFoundation\Cookie;

class SessionService
{
    private $redis;

    public function __construct(RedisClient $redis)
    {
        $this->redis = $redis;
    }

    public function createSession(string $userId): Cookie
    {
        $sessionId = GetUniqueId::generateUniqueSessionId();
        SessionData::storeSessionData($this->redis, $sessionId, $userId);
        return new Cookie('X-Session-ID', $sessionId, time() + 3600);
    }
}
