<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Storelocator
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Storelocator\Block;

/**
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magestore\Storelocator\Model\SystemConfig
     */
    protected $_systemConfig;

    /**
     * @var \Magestore\Storelocator\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magestore\Storelocator\Model\ResourceModel\Store\CollectionFactory
     */
    protected $_storeCollectionFactory;

    /**
     * @var \Magestore\Storelocator\Model\ResourceModel\Tag\CollectionFactory
     */
    protected $_tagCollectionFactory;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Url                      $customerUrl
     * @param array                                            $data
     */
    public function __construct(
        \Magestore\Storelocator\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_systemConfig = $context->getSystemConfig();
        $this->_imageHelper = $context->getImageHelper();
        $this->_storeCollectionFactory = $context->getStoreCollectionFactory();
        $this->_tagCollectionFactory = $context->getTagCollectionFactory();
        $this->_coreRegistry = $context->getCoreRegistry();
    }

    /**
     * @return \Magestore\Storelocator\Model\SystemConfig
     */
    public function getSystemConfig()
    {
        return $this->_systemConfig;
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_systemConfig->isEnableFrontend() ? parent::_toHtml() : '';
    }

    /**
     * @return \Magestore\Storelocator\Model\ResourceModel\Store\Collection
     */
    public function getStoreCollection()
    {
        return $this->_storeCollectionFactory->create();
    }

    /**
     * @return \Magestore\Storelocator\Model\ResourceModel\Tag\Collection
     */
    public function getTagCollection()
    {
        return $this->_tagCollectionFactory->create();
    }

    public function getMediaUrlImage($imagePath = '')
    {
        return $this->_imageHelper->getMediaUrlImage($imagePath);
    }
}
