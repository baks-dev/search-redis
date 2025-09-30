<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\SearchRedis\Messenger\RedisSearchIndex;


use BaksDev\Search\Index\SearchIndexInterface;
use BaksDev\Search\SearchIndex\SearchIndexTagInterface;
use BaksDev\Search\Type\SearchTags\Collection\SearchIndexTagCollection;
use Psr\Log\LoggerInterface;
use Redis;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class RedisSearchIndexDispatcher
{
    private ?string $PASSWORD;

    private int $TABLE;

    public function __construct(
        private SearchIndexTagCollection $SearchIndexTagCollection,
        private SearchIndexInterface $SearchIndex,
        #[Target('searchLogger')] private LoggerInterface $logger,
        #[Autowire(env: 'REDIS_SEARCH_HOST')] private string $HOST,
        #[Autowire(env: 'REDIS_SEARCH_PORT')] private string|int $PORT,
        #[Autowire(env: 'REDIS_SEARCH_PASSWORD')] string|null $PASSWORD = null,
        #[Autowire(env: 'REDIS_SEARCH_TABLE')] string|int|null $TABLE = null,
    )
    {
        $this->PASSWORD = $PASSWORD;
        $this->TABLE = $TABLE ?? 0;
    }

    public function __invoke(RedisSearchIndexMessage $message): void
    {
        /**
         * Очищаем и добавляем индексы поиска
         *
         * @var SearchIndexTagInterface $tag
         */

        $this->clear();

        foreach($this->SearchIndexTagCollection->cases() as $tag)
        {
            $repositoryData = $tag->getRepositoryData();

            if($repositoryData === false)
            {
                continue;
            }

            foreach($repositoryData as $item)
            {
                $this->SearchIndex->addToIndex($tag->prepareDocument($item));
            }

            $this->logger->info(sprintf('Добавили индексы поиска модуля %s', $tag->getModuleName()));
        }
    }

    private function clear(): void
    {
        $redis = new Redis();

        $connect = $redis->connect($this->HOST, (int) $this->PORT, 2.5);

        if(false === $connect)
        {
            $this->logger->critical('Redis connection failed', [self::class.':'.__LINE__]);
            return;
        }

        if(isset($this->PASSWORD))
        {
            $auth = $redis->auth($this->PASSWORD);

            if(false === $auth)
            {
                $this->logger->critical('Ошибка авторизации RedisSearch', [self::class.':'.__LINE__]);
                return;
            }
        }

        // Выбор базы данных (по умолчанию 0)
        if(false === empty($this->TABLE))
        {
            $redis->select($this->TABLE);
        }

        // Получаем все ключи кроме индексных
        $allKeys = $redis->keys('*');

        $dataKeys = [];

        foreach($allKeys as $key)
        {
            if(false === str_starts_with($key, 'ft_'))
            {
                $dataKeys[] = $key;
            }
        }

        // Удаляем данные
        if(false === empty($dataKeys))
        {
            $deleted = $redis->del($dataKeys);

            $this->logger->info(sprintf('Удалено ключей: %s', $deleted));
        }


        $redis->close();
    }
}
