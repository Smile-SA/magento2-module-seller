<?php

namespace Smile\Seller\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\SellerSetup;


class InstallData implements InstallDataInterface
{
    /**
     * @var SellerSetupFactory
     */
    private $sellerySetupFactory;

    /**
     * Init
     *
     * @param SellerSetupFactory $categorySetupFactory
     */
    public function __construct(SellerSetupFactory $sellerySetupFactory)
    {
        $this->sellerSetupFactory = $sellerySetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /**
         * @var SellerSetup $sellerSetup
         */
        $sellerSetup = $this->sellerSetupFactory->create(['setup' => $setup]);
        $sellerSetup->installEntities();
        $setup->endSetup();
    }
}