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
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class RedisSearchIndexHandler
{
    public function __construct(
        #[Target('searchLogger')] private LoggerInterface $logger,
        private SearchIndexTagCollection $SearchIndexTagCollection,
        private SearchIndexInterface $SearchIndex,
    ) {}

    public function __invoke(RedisSearchIndexMessage $message): void
    {
        /** @var SearchIndexTagInterface $tag */
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
}
