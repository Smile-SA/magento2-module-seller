<?php

declare(strict_types=1);

namespace Smile\Seller\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Smile\Seller\Setup\Patch\SellerSetup;
use Smile\Seller\Setup\Patch\SellerSetupFactory;

/**
 * Class default groups and attributes for customer
 */
class DefaultSellerAttributes implements DataPatchInterface, PatchVersionInterface
{
    public function __construct(
        private readonly SellerSetupFactory $sellerSetupFactory,
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply(): self
    {
        /** @var SellerSetup $sellerSetup */
        $sellerSetup = $this->sellerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $sellerSetup->installEntities();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion(): string
    {
        return '2.0.1';
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
