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
use BaksDev\Search\Index\SearchIndexInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group search
 */
#[When(env: 'test')]
class SearchRemoveFromIndexTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {

        $test_product = new AllProductsToIndexResult(
            ProductUid::TEST,
            'Test Product',
            'Test-Product-Article'
        );

        $RedisSearchIndexHandler = self::getContainer()->get(SearchIndexInterface::class);

        /** @var ProductSearchTag $tag */
        $tag = self::getContainer()->get(ProductSearchTag::class);
        $RedisSearchIndexHandler->addToIndex($tag->prepareDocument($test_product));
    }

    public function testUseCase(): void
    {
        self::bootKernel();

        $test_product = new AllProductsToIndexResult(
            ProductUid::TEST,
            'Test Product',
            'Test-Product-Article'
        );

        $RedisSearchIndexHandler = self::getContainer()->get(SearchIndexInterface::class);

        $tag = self::getContainer()->get(ProductSearchTag::class);

        $RedisSearchIndexHandler->removeFromIndex($tag->prepareDocument($test_product));

        self::assertTrue(true);
    }
}