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

namespace BaksDev\SearchRedis\Index;

use BaksDev\Search\EntityDocument\EntityDocumentInterface;
use BaksDev\Search\Index\SearchIndexInterface;
use BaksDev\SearchRedis\RediSearch\Index;
use BaksDev\SearchRedis\RediSearch\Query\BuilderInterface;
use BaksDev\SearchRedis\RedisRaw\PredisAdapter;
use BaksDev\SearchRedis\RedisRaw\RedisRawClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;

/**
 * Класс для работы RediSearch
 *
 * Включает методы для инициализации клиента,
 * добавления в индекс, удаления,
 * а также метод для получения результатов по поисковому слову
 */
final class RedisSearchIndexHandler implements SearchIndexInterface
{
    private RedisRawClientInterface $client;

    private Index $index;

    private int $max;

    private int $offset;

    public function __construct(
        #[Target('SearchLogger')] private readonly LoggerInterface $logger,
        #[Autowire(env: 'REDIS_SEARCH_HOST')] string $HOST,
        #[Autowire(env: 'REDIS_SEARCH_PORT')] string|int $PORT,
        #[Autowire(env: 'REDIS_SEARCH_PASSWORD')] string $PASSWORD,
        #[Autowire(env: 'REDIS_SEARCH_TABLE')] string|int|null $TABLE = null,
    )
    {
        $this->initClient($HOST, (int) $PORT, (int) $TABLE, $PASSWORD);
        $this->initIndex();

        $this->offset = 0;
        $this->max = 100;

    }

    public function initClient(string $host, int $port, int $table, string $password): void
    {
        $this->client =
            new PredisAdapter($this->logger)
                ->connect($host, $port, $table, $password);
    }

    /**
     * Добавление необходимых полей в индекс
     * и при необходимости создание
     */
    private function initIndex(): void
    {
        $this->index =
            new Index($this->client)
                ->addTextField('entity_index')
                ->addTagField('search_tag');

        //        $this->index->drop(); die();
        if(!$this->index->exists())
        {
            $this->index->create();
        }
    }

    public function addToIndex(EntityDocumentInterface $entityDocument): void
    {
        $this->index->delete($entityDocument->getId(), true);
        $this->index->add($entityDocument);
    }

    /**
     * Удалить из индекса
     */
    public function removeFromIndex(EntityDocumentInterface $entityDocument): void
    {
        $this->index->delete($entityDocument->getId(), true);
    }


    public function maxResult(int $max): void
    {
        $this->max = $max;
    }

    public function setOffset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }


    /**
     * Получить результаты по поисковому слову, с учетов тегов
     */
    public function handleSearchQuery(?string $search = null, ?string $searchModule = null): bool|array
    {
        /** @var BuilderInterface $builder */
        $builder = $this->index->noContent()
            ->limit($this->offset, $this->max);

        if(null !== $searchModule)
        {
            $builder->tagFilter('search_tag', [$searchModule]);
        }

        $result = $builder->search($search);

        return $result->getCount() ? $result->getDocuments() : false;
    }
}