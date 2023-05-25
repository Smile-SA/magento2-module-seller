<?php

namespace Smile\Seller\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Seller Data install class.
 */
class InstallData implements InstallDataInterface
{
    public function __construct(private SellerSetupFactory $sellerSetupFactory)
    {
    }

    /**
     * @inheritdoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var SellerSetup $sellerSetup */
        $sellerSetup = $this->sellerSetupFactory->create(['setup' => $setup]);
        $sellerSetup->installEntities();

        $setup->endSetup();
    }
}
