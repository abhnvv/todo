<?php
namespace App\Redis;

use Predis\Client;


class RedisClient {
    private static $instance;
    private $redis;

    public function __construct() {
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6381,
        ]);
    }

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getRedis() {
        return $this->redis;
    }
    public function removeKey(string $redisKey) {
        $this->redis->del($redisKey);
    }
}
