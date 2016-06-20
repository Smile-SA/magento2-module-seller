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

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Validator\UniversalFactory;
use Psr\Log\LoggerInterface;

/**
 * Sellers Collection
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Collection extends AbstractCollection
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
     * @var null Attribute set id of the entity
     */
    private $sellerAttributeSetId = null;

    /**
     * @var null Attribute set name of the entity
     */
    private $sellerAttributeSetName = null;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory             $entityFactory    Entity Factory
     * @param \Psr\Log\LoggerInterface                                     $logger           Logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy    Fetch Strategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager     Event Manager
     * @param \Magento\Eav\Model\Config                                    $eavConfig        EAV Config
     * @param \Magento\Framework\App\ResourceConnection                    $resource         Resource Connection
     * @param \Magento\Eav\Model\EntityFactory                             $eavEntityFactory EAV Entity Factory
     * @param \Magento\Eav\Model\ResourceModel\Helper                      $resourceHelper   Resource Helper
     * @param \Magento\Framework\Validator\UniversalFactory                $universalFactory Universal Factory
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null          $connection       Database Connection
     * @param null                                                         $attributeSetName Seller Attribute Set Name
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        ResourceConnection $resource,
        EavEntityFactory $eavEntityFactory,
        Helper $resourceHelper,
        UniversalFactory $universalFactory,
        AdapterInterface $connection = null,
        $attributeSetName = null
    ) {
        $this->sellerAttributeSetName = $attributeSetName;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $connection
        );
    }

    /**
     * Init collection and determine table names
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Smile\Seller\Model\Seller', 'Smile\Seller\Model\ResourceModel\Seller');
        if ($this->sellerAttributeSetId == null) {
            if ($this->sellerAttributeSetName !== null) {
                $this->sellerAttributeSetId = $this->getResource()->getAttributeSetIdByName($this->sellerAttributeSetName);
            }
        }
    }

    /**
     * Init select. Retrieve only sellers of current attribute set if specified.
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        if ($this->sellerAttributeSetId !== null) {
            $this->addFieldToFilter('attribute_set_id', (int) $this->sellerAttributeSetId);
        }

        return $this;
    }
}
