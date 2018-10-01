<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Block\Brand;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Brand;
use Aheadworks\ShopByBrand\Model\Template\FilterProvider;
use Aheadworks\ShopByBrand\Model\Url;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Info
 *
 * @method string getImageType()
 *
 * @package Aheadworks\ShopByBrand\Block\Brand
 */
class Info extends \Magento\Framework\View\Element\Template implements IdentityInterface
{
    /**
     * @var {@inheritdoc}
     */
    protected $_template = 'brand/info.phtml';

    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepository;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @param Context $context
     * @param BrandRepositoryInterface $brandRepository
     * @param Url $url
     * @param FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        BrandRepositoryInterface $brandRepository,
        Url $url,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->brandRepository = $brandRepository;
        $this->url = $url;
        $this->filterProvider = $filterProvider;
    }

    /**
     * Get brand instance
     *
     * @return BrandInterface
     */
    public function getBrand()
    {
        if (!$this->hasData('brand')) {
            $brandId = $this->getRequest()->getParam('brand_id');
            $this->setData('brand', $this->brandRepository->get($brandId));
        }
        return $this->getData('brand');
    }

    /**
     * Get brand page url
     *
     * @return string
     */
    public function getBrandUrl()
    {
        return $this->url->getBrandUrl($this->getBrand());
    }

    /**
     * Get brand logo url
     *
     * @return string
     */
    public function getBrandLogoUrl()
    {
        return $this->url->getLogoUrl($this->getBrand()->getLogo(), $this->getImageType());
    }

    /**
     * Get brand description html
     *
     * @return string
     */
    public function getDescriptionHtml()
    {
        return $this->filterProvider->getFilter()
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->filter($this->getBrand()->getDescription());
    }

    /**
     * Check if description should be wrapped
     *
     * @return bool
     */
    public function wrapDescription()
    {
        return $this->hasData('wrap_description')
            ? $this->getData('wrap_description')
            : false;
    }

    /**
     * Check if can show brand description
     *
     * @return bool
     */
    public function canShowDescription()
    {
        $canShowDescription = $this->hasData('can_show_description')
            ? $this->getData('can_show_description')
            : true;
        return $canShowDescription && !empty($this->getBrand()->getDescription());
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->getBrand()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [
            Brand::CACHE_TAG . '_' . $this->getBrand()->getBrandId()
        ];
    }
}
