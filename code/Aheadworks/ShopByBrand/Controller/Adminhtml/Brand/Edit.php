<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Controller\Adminhtml\Brand;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterfaceFactory;
use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit
 * @package Aheadworks\ShopByBrand\Controller\Adminhtml\Brand
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Edit extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_ShopByBrand::brands';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var BrandInterfaceFactory
     */
    private $brandFactory;

    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepository;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param BrandInterfaceFactory $brandFactory
     * @param BrandRepositoryInterface $brandRepository
     * @param Registry $coreRegistry
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        BrandInterfaceFactory $brandFactory,
        BrandRepositoryInterface $brandRepository,
        Registry $coreRegistry,
        DataObjectProcessor $dataObjectProcessor,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->brandFactory = $brandFactory;
        $this->brandRepository = $brandRepository;
        $this->coreRegistry = $coreRegistry;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $brandId = (int)$this->getRequest()->getParam('brand_id');
        /** @var BrandInterface $brand */
        $brand = $this->brandFactory->create();
        if ($brandId) {
            try {
                $brand = $this->brandRepository->get($brandId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while editing the brand.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
        }

        $this->registerBrandContentData($brand);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage
            ->setActiveMenu('Aheadworks_ShopByBrand::brands')
            ->getConfig()->getTitle()->prepend(
                $brandId ? __('Edit Brand') : __('New Brand')
            );
        return $resultPage;
    }

    /**
     * Register brand content data
     *
     * @param BrandInterface $brand
     * @return void
     */
    private function registerBrandContentData(BrandInterface $brand)
    {
        $brandData = $this->dataPersistor->get('aw_brand')
            ? $this->dataPersistor->get('aw_brand')
            : $this->dataObjectProcessor->buildOutputDataArray(
                $brand,
                BrandInterface::class
            );
        $brandContentData = isset($brandData['content'])
            ? $brandData['content']
            : [];
        $this->coreRegistry->register('aw_brand_content', $brandContentData);
    }
}
