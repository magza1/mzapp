<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Controller\Adminhtml\Brand;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class Delete
 * @package Aheadworks\ShopByBrand\Controller\Adminhtml\Brand
 */
class Delete extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_ShopByBrand::brands';

    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepository;

    /**
     * @param Context $context
     * @param BrandRepositoryInterface $brandRepository
     */
    public function __construct(
        Context $context,
        BrandRepositoryInterface $brandRepository
    ) {
        parent::__construct($context);
        $this->brandRepository = $brandRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $brandId = (int)$this->getRequest()->getParam('brand_id');
        if ($brandId) {
            try {
                $this->brandRepository->deleteById($brandId);
                $this->messageManager->addSuccessMessage(__('Brand was successfully deleted.'));
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Brand could not be deleted.')
                );
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
