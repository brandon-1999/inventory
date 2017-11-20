<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Integration\Stock;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Inventory\Indexer\Stock\StockIndexer;
use Magento\Inventory\Model\ReservationCleanupInterface;
use Magento\Inventory\Test\Integration\Indexer\RemoveIndexData;
use Magento\InventoryApi\Api\IsProductInStockInterface;
use Magento\InventoryApi\Api\ReservationBuilderInterface;
use Magento\InventoryApi\Api\ReservationsAppendInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IsProductInStockTest extends TestCase
{
    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var ReservationsAppendInterface
     */
    private $reservationsAppend;

    /**
     * @var ReservationCleanupInterface
     */
    private $reservationCleanup;

    /**
     * @var IsProductInStockInterface
     */
    private $isProductInStock;

    /**
     * @var RemoveIndexData
     */
    private $removeIndexData;

    protected function setUp()
    {
        $this->indexer = Bootstrap::getObjectManager()->create(IndexerInterface::class);
        $this->indexer->load(StockIndexer::INDEXER_ID);

        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->reservationsAppend = Bootstrap::getObjectManager()->get(ReservationsAppendInterface::class);
        $this->reservationCleanup = Bootstrap::getObjectManager()->get(ReservationCleanupInterface::class);
        $this->isProductInStock = Bootstrap::getObjectManager()->get(IsProductInStockInterface::class);

        $this->removeIndexData = Bootstrap::getObjectManager()->create(RemoveIndexData::class);
        $this->removeIndexData->execute([10, 20, 30]);
    }

    public function tearDown()
    {
        $this->removeIndexData->execute([10, 20, 30]);
        $this->reservationCleanup->execute();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testProductIsInStock()
    {
        $this->indexer->reindexRow(10);

        $this->reservationsAppend->execute([
            // reserve 5 units
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(-5)->build(),
            // unreserve 1.5 units
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(1.5)->build(),
        ]);

        self::assertTrue($this->isProductInStock->execute('SKU-1', 10));

        $this->reservationsAppend->execute([
            // unreserve 3.5 units
            $this->reservationBuilder->setStockId(1)->setSku('SKU-1')->setQuantity(3.5)->build(),
        ]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testProductIsNotInStock()
    {
        $this->indexer->reindexRow(10);

        $this->reservationsAppend->execute([
            // reserve 8.5 units
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(-8.5)->build(),
        ]);

        self::assertFalse($this->isProductInStock->execute('SKU-1', 10));

        $this->reservationsAppend->execute([
            // unreserve 8.5 units
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(8.5)->build(),
        ]);
    }
}
