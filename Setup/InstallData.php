<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\Seller\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Seller Data install class.
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var SellerSetupFactory
     */
    private $sellerSetupFactory;

    /**
     * InstallData constructor
     *
     * @param SellerSetupFactory $sellerSetupFactory The Seller Setup factory
     */
    public function __construct(SellerSetupFactory $sellerSetupFactory)
    {
        $this->sellerSetupFactory = $sellerSetupFactory;
    }

    /**
     * {@inheritDoc}
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
