<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Block\Adminhtml\Brand\Edit;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 * @package Aheadworks\ShopByBrand\Block\Adminhtml\Brand\Edit
 */
class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepository;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param BrandRepositoryInterface $brandRepository
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        BrandRepositoryInterface $brandRepository
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->brandRepository = $brandRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $brandId = $this->request->getParam('brand_id');
        if ($brandId && $this->brandRepository->get($brandId)) {
            $confirmMessage = __('Are you sure you want to do this?');
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => sprintf(
                    "deleteConfirm('%s', '%s')",
                    $confirmMessage,
                    $this->urlBuilder->getUrl('*/*/delete', ['brand_id' => $brandId])
                ),
                'sort_order' => 20
            ];
        }
        return $data;
    }
}
