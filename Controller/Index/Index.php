<?php

namespace Smile\Seller\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\EntityManager\EntityManager;
use Smile\Seller\Api\Data\SellerInterfaceFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    private $entityManager;

    /**
     * @var \Smile\Seller\Api\Data\SellerInterfaceFactory;
     */
    private $sellerFactory;

    public function __construct(Context $context, EntityManager $entityManager, SellerInterfaceFactory $sellerFactory)
    {
        parent::__construct($context);
        $this->entityManager = $entityManager;
        $this->sellerFactory = $sellerFactory;
    }

    /**
     * Index action
     *
     * @return $this
     */
    public function execute()
    {
        $seller = $this->sellerFactory->create();
        $seller->setName('Test ' . rand(1, 1000000));
        $seller->setSellerCode('Test ' . rand(1, 1000000));
        $this->entityManager->save($seller);

        $seller = $this->sellerFactory->create();
        $seller->setStoreId(1);
        $seller->setName('Test ' . rand(1, 1000000));
        $seller->setSellerCode('Test ' . rand(1, 1000000));
        $this->entityManager->save($seller);

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('/');
    }
}
