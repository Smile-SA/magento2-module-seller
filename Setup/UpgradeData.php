<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\StoreLocator
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Data Upgrade for Seller
 *
 * @category Smile
 * @package  Smile\StoreLocator
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Smile\Seller\Setup\SellerSetup
     */
    private $sellerSetup;

    /**
     * Constructor.
     *
     * @param EavSetupFactory          $eavSetupFactory          EAV Setup Factory.
     * @param StoreLocatorSetupFactory $sellerSetupFactory       The Seller Setup Factory
     */
    public function __construct(EavSetupFactory $eavSetupFactory, SellerSetupFactory $sellerSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->sellerSetup = $sellerSetupFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->sellerSetup->addImage($eavSetup);
        }
    }
}
