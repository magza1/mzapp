<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Block\Adminhtml\Brand;

use Aheadworks\ShopByBrand\Block\Adminhtml\Brand\Products;
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Block\Template\Context;
use Aheadworks\ShopByBrand\Model\ResourceModel\Product\Collection;

/**
 * Class AssignProducts
 * @package Aheadworks\ShopByBrand\Block\Adminhtml\Brand
 */
class AssignProducts extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'brand/assign_products.phtml';

    /**
     * @var Products
     */
    protected $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var Collection
     */
    private $productCollection;

    /**
     * @var bool
     */
    private $canShow = false;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Collection $productCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Collection $productCollection,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->productCollection = $productCollection;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                Products::class,
                'aw.brand.products.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        /** @var Products $blockGrid */
        $blockGrid = $this->getBlockGrid();
        if ($blockGrid->getBrand() && $blockGrid->getBrand()->getBrandId()) {
            $this->canShow = true;
            return $blockGrid->toHtml();
        }
        return '';
    }

    /**
     * @return string
     */
    public function getProductsJson()
    {
        if (!$this->getBlockGrid()->getBrand()) {
            return '{}';
        }
        $brand = $this->getBlockGrid()->getBrand();
        $products = $this->productCollection->getSelectedProductsPositions($brand);
        if (!empty($products)) {
            return $this->jsonEncoder->encode($products);
        }
        return '{}';
    }

    /**
     * @return bool
     */
    public function canShow()
    {
        return $this->canShow;
    }
}
