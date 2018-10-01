<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsProduct\Controller\Vendors\Product;

use Magento\Framework\Controller\ResultFactory;

/**
 * Backend reload of product create/edit form
 */
class Reload extends \Vnecoms\VendorsProduct\Controller\Vendors\Product
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('set')) {
            return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
        }

        $product = $this->productBuilder->build($this->getRequest());

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $resultLayout->getLayout()->getUpdate()->addHandle(['catalog_product_' . $product->getTypeId()]);
        $resultLayout->getLayout()->getUpdate()->removeHandle('default');
        $resultLayout->setHeader('Content-Type', 'application/json', true);
        return $resultLayout;
    }
}
