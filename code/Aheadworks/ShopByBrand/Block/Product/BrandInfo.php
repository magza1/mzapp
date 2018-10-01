<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Block\Product;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Block\Brand\Info as BrandInfoBlock;
use Aheadworks\ShopByBrand\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;

/**
 * Class BrandInfo
 *
 * @method string getPosition()
 *
 * @package Aheadworks\ShopByBrand\Block\Product
 */
class BrandInfo extends AbstractBlock implements IdentityInterface
{
    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param BrandRepositoryInterface $brandRepository
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        BrandRepositoryInterface $brandRepository,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->brandRepository = $brandRepository;
        $this->config = $config;
    }

    /**
     * Get brand instance
     *
     * @return BrandInterface|null
     */
    private function getBrand()
    {
        try {
            return $this->brandRepository->getByProductId(
                $this->getRequest()->getParam('id')
            );
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        $canShow = $this->getBrand()
            && $this->getPosition() == $this->config->getProductPageBrandInfoBlockPosition();
        if ($canShow) {
            return $this->getLayout()->createBlock(
                BrandInfoBlock::class,
                '',
                [
                    'data' => [
                        'brand' => $this->getBrand(),
                        'wrap_description' => true,
                        'can_show_description' => $this->config->isDisplayProductPageBrandDescription(),
                        'image_type' => 'small_image'
                    ]
                ]
            )->toHtml();
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $identities = [];
        $productId = $this->getRequest()->getParam('id');
        if ($productId) {
            $identities[] = Product::CACHE_TAG . '_' . $productId;
        }
        return $identities;
    }
}
