<?php

namespace BaksDev\SearchRedis\RediSearch;

use BaksDev\SearchRedis\RediSearch\Exceptions\AliasDoesNotExistException;
use BaksDev\SearchRedis\RediSearch\Exceptions\DocumentAlreadyInIndexException;
use BaksDev\SearchRedis\RediSearch\Exceptions\RediSearchException;
use BaksDev\SearchRedis\RediSearch\Exceptions\UnknownIndexNameException;
use BaksDev\SearchRedis\RediSearch\Exceptions\UnknownIndexNameOrNameIsAnAliasItselfException;
use BaksDev\SearchRedis\RediSearch\Exceptions\UnknownRediSearchCommandException;
use BaksDev\SearchRedis\RediSearch\Exceptions\UnsupportedRediSearchLanguageException;
use BaksDev\SearchRedis\RedisRaw\AbstractRedisRawClient;
use BaksDev\SearchRedis\RedisRaw\Exceptions\RawCommandErrorException;
use BaksDev\SearchRedis\RedisRaw\RedisRawClientInterface;
use Exception;
use Psr\Log\LoggerInterface;

class RediSearchRedisClient implements RedisRawClientInterface
{
    /** @var AbstractRedisRawClient */
    protected $redis;

    public function __construct(RedisRawClientInterface $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @throws RediSearchException
     * @throws DocumentAlreadyInIndexException
     * @throws UnknownIndexNameException
     * @throws UnsupportedRediSearchLanguageException
     * @throws AliasDoesNotExistException
     * @throws UnknownRediSearchCommandException
     * @throws UnknownIndexNameOrNameIsAnAliasItselfException
     */
    public function validateRawCommandResults($rawResult, string $command, array $arguments)
    {
        $isRawResultException = $rawResult instanceof Exception;
        $message = $isRawResultException ? $rawResult->getMessage() : $rawResult;

        if (!is_string($message)) {
            return;
        }

        $message = strtolower($message);

        if ($message === 'unknown index name') {
            throw new UnknownIndexNameException();
        }

        if (in_array($message, ['no such language', 'unsupported language', 'unsupported stemmer language', 'bad argument for `language`'])) {
            throw new UnsupportedRediSearchLanguageException();
        }

        if ($message === 'unknown index name (or name is an alias itself)') {
            throw new UnknownIndexNameOrNameIsAnAliasItselfException();
        }

        if ($message === 'alias does not exist') {
            throw new AliasDoesNotExistException();
        }

        if (strpos($message, 'err unknown command \'ft.') !== false) {
            throw new UnknownRediSearchCommandException($message);
        }

        if (in_array($message, ['document already in index', 'document already exists'])) {
            throw new DocumentAlreadyInIndexException($arguments[0], $arguments[1]);
        }

        throw new RediSearchException($rawResult);
    }

    public function connect($hostname = '127.0.0.1', $port = 6379, $db = 0, $password = null): RedisRawClientInterface
    {
        $this->redis->connect($hostname, $port, $db, $password);
        return $this;
    }

    public function flushAll()
    {
        $this->redis->flushAll();
    }

    public function multi(bool $usePipeline = false)
    {
        return $this->redis->multi($usePipeline);
    }

    /**
     * @throws RediSearchException
     * @throws DocumentAlreadyInIndexException
     * @throws UnknownIndexNameException
     * @throws UnsupportedRediSearchLanguageException
     * @throws AliasDoesNotExistException
     * @throws UnknownRediSearchCommandException
     * @throws UnknownIndexNameOrNameIsAnAliasItselfException
     */
    public function rawCommand(string $command, array $arguments = [])
    {
        try {
            foreach ($arguments as $index => $value) {
                /* The various RedisRaw clients have different expectations about arg types, but generally they all
                 * agree that they can be strings.
                 */
                $arguments[$index] = (string) $value;
            }

            /**
             *
             * @see PredisAdapter
             * @see RedisClientAdapter
             */
            $result = $this->redis->rawCommand($command, $arguments);

        } catch (RawCommandErrorException $exception) {
            $result = $exception->getPrevious()?->getMessage();
        }

        if ($command !== 'FT.EXPLAIN') {
            $this->validateRawCommandResults($result, $command, $arguments);
        }

        return $result;
    }

    public function setLogger(LoggerInterface $logger): RedisRawClientInterface
    {
        return $this->redis->setLogger($logger);
    }

    public function prepareRawCommandArguments(string $command, array $arguments): array
    {
        return $this->prepareRawCommandArguments($command, $arguments);
    }
}
