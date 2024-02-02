<?php
namespace App\Utils;

use App\Redis\RedisClient;

class SessionData
{
    public static function storeSessionData(RedisClient $redis, string $sessionId, string $userId): void
    {
        $redisKey = 'sessionId:' . $sessionId;
        $sessionData = ['userId' => $userId];
        $redis->getRedis()->set($redisKey, json_encode($sessionData), 'EX', 3600);
    }
    public static function getSessionData(RedisClient $redis, string $sessionId): string
    {
        $redisKey = 'sessionId:' . $sessionId;
        $sessionData = $redis->getRedis()->get($redisKey);

        if(!$sessionData) return null;

        $sessionDataArray = json_decode($sessionData, true);
        return $sessionDataArray['userId'];
    }
}