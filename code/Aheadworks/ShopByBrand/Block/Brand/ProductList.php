<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Block\Brand;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Config;
use Aheadworks\ShopByBrand\Model\Config\Source\ProductPage\MoreFromThisBrand\BlockLayout;
use Aheadworks\ShopByBrand\Model\Config\Source\ProductPage\MoreFromThisBrand\SortBy;
use Aheadworks\ShopByBrand\Model\ResourceModel\Product\Collection;
use Aheadworks\ShopByBrand\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class ProductList
 *
 * @method string getPosition()
 *
 * @package Aheadworks\ShopByBrand\Block\Brand
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductList extends \Magento\Framework\View\Element\Template implements IdentityInterface
{
    /**
     * Displayed product items default limit
     */
    const DEFAULT_ITEMS_LIMIT = 10;

    /**
     * Product listing images identifier
     */
    const IMAGE_ID = 'related_products_list';

    /**
     * @var {@inheritdoc}
     */
    protected $_template = 'brand/product/list.phtml';

    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepository;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProductUrl
     */
    private $productUrl;

    /**
     * @var ImageBuilder
     */
    private $imageBuilder;

    /**
     * @var CartHelper
     */
    private $cartHelper;

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * @var ProductVisibility
     */
    private $productVisibility;

    /**
     * @var Product[]
     */
    private $items;

    /**
     * @param Context $context
     * @param BrandRepositoryInterface $brandRepository
     * @param CollectionFactory $productCollectionFactory
     * @param Config $config
     * @param ProductUrl $productUrl
     * @param ImageBuilder $imageBuilder
     * @param CartHelper $cartHelper
     * @param PostHelper $postHelper
     * @param CatalogConfig $catalogConfig
     * @param ProductVisibility $productVisibility
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        BrandRepositoryInterface $brandRepository,
        CollectionFactory $productCollectionFactory,
        Config $config,
        ProductUrl $productUrl,
        ImageBuilder $imageBuilder,
        CartHelper $cartHelper,
        PostHelper $postHelper,
        CatalogConfig $catalogConfig,
        ProductVisibility $productVisibility,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->brandRepository = $brandRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->config = $config;
        $this->productUrl = $productUrl;
        $this->imageBuilder = $imageBuilder;
        $this->cartHelper = $cartHelper;
        $this->postHelper = $postHelper;
        $this->catalogConfig = $catalogConfig;
        $this->productVisibility = $productVisibility;
    }

    /**
     * Get product items
     *
     * @return Product[]
     */
    public function getItems()
    {
        if (!$this->items) {
            $brand = $this->getBrand();
            if ($brand) {
                /** @var Collection $collection */
                $collection = $this->productCollectionFactory->create();
                $collection
                    ->addBrandFilter($brand)
                    ->addFieldToFilter('entity_id', ['neq' => $this->getProductId()])
                    ->setPageSize($this->getItemsLimit())
                    ->addMinimalPrice()
                    ->addFinalPrice()
                    ->addTaxPercents()
                    ->addUrlRewrite()
                    ->addStoreFilter()
                    ->addAttributeToSelect('required_options')
                    ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                    ->setVisibility($this->productVisibility->getVisibleInCatalogIds());
                $this->initSorting($collection);
                $this->items = $collection->getItems();
            } else {
                $this->items = [];
            }
        }
        return $this->items;
    }

    /**
     * Get collection items limit
     *
     * @return int
     */
    private function getItemsLimit()
    {
        return $this->config->getMoreFromThisBrandBlockLayout() == BlockLayout::SINGLE_ROW
            ? self::DEFAULT_ITEMS_LIMIT
            : $this->config->getMoreFromThisBrandBlockProductsLimit();
    }

    /**
     * Init collection items sorting
     *
     * @param Collection $collection
     * @return void
     */
    private function initSorting($collection)
    {
        switch ($this->config->getMoreFromThisBrandSortProductsBy()) {
            case SortBy::BESTSELLERS:
                $collection->addSortingByBestsellers();
                break;
            case SortBy::NEWEST:
                $collection->addSortingByNewest();
                break;
            case SortBy::PRICE_ASC:
                $collection->addSortingByPriceAsc();
                break;
            case SortBy::PRICE_DESC:
                $collection->addSortingByPriceDesc();
                break;
            case SortBy::RANDOM:
                $collection->addRandomSorting();
                break;
        }
    }

    /**
     * Get block title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->config->getMoreFromThisBrandBlockName();
    }

    /**
     * Get product list layout type
     *
     * @return string
     */
    public function getProductListLayoutType()
    {
        return $this->config->getMoreFromThisBrandBlockLayout();
    }

    /**
     * Get product ID
     *
     * @return int|null
     */
    private function getProductId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * Get brand instance
     *
     * @return BrandInterface|null
     */
    private function getBrand()
    {
        try {
            return $this->brandRepository->getByProductId($this->getProductId());
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get product URL
     *
     * @param Product $product
     * @param array $additional
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        if (!isset($additional['_escape'])) {
            $additional['_escape'] = true;
        }
        return $this->productUrl->getUrl($product, $additional);
    }

    /**
     * Check if display Add To Cart button
     *
     * @return bool
     */
    public function isDisplayAddToCart()
    {
        return $this->config->isMoreFromThisBrandBlockAddToCartEnabled();
    }

    /**
     * Get add to cart url
     *
     * @param Product $product
     * @return string
     */
    public function getAddToCartUrl($product)
    {
        if ($product->getTypeInstance()->hasRequiredOptions($product)) {
            return $this->getProductUrl(
                $product,
                [
                    '_escape' => true,
                    '_query' => ['options' => 'cart']
                ]
            );
        }
        return $this->cartHelper->getAddUrl($product);
    }

    /**
     * Get add to cart post data
     *
     * @param Product $product
     * @return string
     */
    public function getAddToCartPostData($product)
    {
        return $this->postHelper->getPostData(
            $this->getAddToCartUrl($product),
            ['product' => $product->getEntityId()]
        );
    }

    /**
     * Get product image
     *
     * @param Product $product
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product)
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId(self::IMAGE_ID)
            ->setAttributes([])
            ->create();
    }

    /**
     * Get product price block html
     *
     * @param Product $product
     * @return string
     */
    public function getProductPriceHtml(Product $product)
    {
        $html = '';
        /** @var PricingRender $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if ($priceRender) {
            $html = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                ['zone' => PricingRender::ZONE_ITEM_LIST]
            );
        }
        return $html;

    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->config->isDisplayMoreFromThisBrandBlock()
            && count($this->getItems())
            && $this->getPosition() == $this->config->getMoreFromThisBrandBlockPosition()
        ) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $identities = [Product::CACHE_TAG . '_' . $this->getProductId()];
        if (count($this->getItems())) {
            foreach ($this->getItems() as $item) {
                $identities = array_merge($identities, $item->getIdentities());
            }
        } else {
            $identities[] = Product::CACHE_TAG;
        }
        return $identities;
    }
}
