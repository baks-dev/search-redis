<?php

namespace BaksDev\SearchRedis\RedisRaw;

use RedisClient\RedisClient;
use RedisClient\Exception\ErrorResponseException;

/**
 * Class RedisClientAdapter
 * @package BaksDev\SearchRedis\RedisRaw
 *
 * This class wraps the Cheprasov client: https://github.com/cheprasov/php-redis-client
 */
class RedisClientAdapter extends AbstractRedisRawClient
{
    /** @var RedisClient */
    public $redis;

    public function connect($hostname = '127.0.0.1', $port = 6379, $db = 0, $password = null): RedisRawClientInterface
    {
        $this->redis = new RedisClient([
            'server' => "$hostname:$port",
            'database' => $db,
            'password' => $password,
        ]);

        return $this;
    }

    public function multi(bool $usePipeline = false)
    {
        return $this->redis->pipeline();
    }

    /**
     * @throws Exceptions\UnsupportedRedisDatabaseException
     * @throws Exceptions\RawCommandErrorException
     */
    public function rawCommand(string $command, array $arguments)
    {
        $arguments = $this->prepareRawCommandArguments($command, $arguments);
        $rawResult = null;
        try {
            $rawResult = $this->redis->executeRaw($arguments);
        } catch (ErrorResponseException $exception) {
            $this->validateRawCommandResults($exception, $command, $arguments);
        }
        return $this->normalizeRawCommandResult($rawResult);
    }
}
