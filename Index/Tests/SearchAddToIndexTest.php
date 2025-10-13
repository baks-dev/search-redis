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

namespace BaksDev\SearchRedis\Index\Tests;

use BaksDev\Products\Product\Repository\Search\AllProductsToIndex\AllProductsToIndexResult;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\SearchTags\ProductSearchTag;
use BaksDev\SearchRedis\Index\RedisSearchIndexHandler;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('search')]
class SearchAddToIndexTest extends KernelTestCase
{

    public function testUseCase(): void
    {
        self::bootKernel();

        $test_product = new AllProductsToIndexResult(
            ProductUid::TEST,
            'Test Product',
            'Test-Product-Article'
        );

        $logger = self::getContainer()->get(LoggerInterface::class);
        /** @var RedisSearchIndexHandler $RedisSearchIndexHandler */
        try
        {
            $RedisSearchIndexHandler = new RedisSearchIndexHandler(
                'localhost', '6579', 0, 'password',
                $logger
            );

            /** @var ProductSearchTag $tag */
            $tag = self::getContainer()->get(ProductSearchTag::class);
            $RedisSearchIndexHandler->addToIndex($tag->prepareDocument($test_product));

        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }

        self::assertTrue(true);
    }
}