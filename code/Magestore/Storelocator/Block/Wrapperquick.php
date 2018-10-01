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

use Magento\Framework\Registry;
/**
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class Wrapperquick extends \Magestore\Storelocator\Block\AbstractBlock
{
    protected $_template = 'Magestore_Storelocator::wrapperquick.phtml';

    /**
     * Block constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        \Magestore\Storelocator\Block\Context $context,
		
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
	
	

    protected function _prepareLayout()
    {
		
			$this->addChild('storelocator.mapbox', 'Magestore\Storelocator\Block\ListStore\MapBoxquick');
			$this->addChild('storelocator.searchbox', 'Magestore\Storelocator\Block\ListStore\SearchBoxquick');
			$this->addChild('storelocator.liststorebox', 'Magestore\Storelocator\Block\ListStore\ListStoreBoxquick');
		
        return parent::_prepareLayout();
    }
}
