<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Controller\Adminhtml\Brand;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterfaceFactory;
use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor\Composite as PostDataProcessor;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\Page;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Error;
use Magento\Framework\Validator\Exception as ValidatorException;

/**
 * Class Save
 * @package Aheadworks\ShopByBrand\Controller\Adminhtml\Brand
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_ShopByBrand::brands';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var PostDataProcessor
     */
    private $postDataProcessor;

    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepository;

    /**
     * @var BrandInterfaceFactory
     */
    private $brandFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param PostDataProcessor $postDataProcessor
     * @param BrandRepositoryInterface $brandRepository
     * @param BrandInterfaceFactory $brandFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        PostDataProcessor $postDataProcessor,
        BrandRepositoryInterface $brandRepository,
        BrandInterfaceFactory $brandFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->postDataProcessor = $postDataProcessor;
        $this->brandRepository = $brandRepository;
        $this->brandFactory = $brandFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $requestData = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($requestData) {
            $entityData = $this->postDataProcessor->prepareEntityData($requestData);
            try {
                $brand = $this->performSave($entityData);

                $this->dataPersistor->clear('aw_brand');
                $this->messageManager->addSuccessMessage(__('The brand was successfully saved.'));

                $back = $this->getRequest()->getParam('back');
                if ($back == 'edit') {
                    return $resultRedirect->setPath(
                        '*/*/' . $back,
                        [
                            'brand_id' => $brand->getBrandId(),
                            '_current' => true
                        ]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (ValidatorException $exception) {
                $this->addValidationMessages($exception);
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while saving the brand.')
                );
            }

            $this->dataPersistor->set('aw_brand', $entityData);

            if (isset($entityData['brand_id'])) {
                return $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        'brand_id' => $entityData['brand_id'],
                        '_current' => true
                    ]
                );
            }
            return $resultRedirect->setPath('*/*/new', ['_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Perform save
     *
     * @param array $data
     * @return BrandInterface
     */
    private function performSave($data)
    {
        $brandId = $data['brand_id'];
        $brand = $brandId
            ? $this->brandRepository->get($brandId)
            : $this->brandFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $brand,
            $data,
            BrandInterface::class
        );
        return $this->brandRepository->save($brand);
    }

    /**
     * Add validator exceptions message to message collection
     *
     * @param ValidatorException $exception
     * @return void
     */
    private function addValidationMessages(ValidatorException $exception)
    {
        $messages = $exception->getMessages();
        if (empty($messages)) {
            $messages = [$exception->getMessage()];
        }
        foreach ($messages as $message) {
            if (!$message instanceof Error) {
                $message = new Error($message);
            }
            $this->messageManager->addMessage($message);
        }
    }
}
