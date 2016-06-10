<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Model\ResourceModel\Seller;

/**
 * Sellers Collection
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Collection extends \Magento\Eav\Model\Entity\Collection\AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'smile_seller_entity_collection';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'smile_seller_entity_collection';

    /**
     * Init collection and determine table names
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Smile\Seller\Model\Seller', 'Smile\Seller\Model\ResourceModel\Seller');
    }

}
