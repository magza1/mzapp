<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsMessage\Block\Adminhtml\Messages;

/**
 * Vendor Notifications block
 */
class View extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Get message
     *
     * @return \Vnecoms\VendorsMessage\Model\Message
     */
    public function getMessage(){

        return $this->_coreRegistry->registry('message');
    }

    /**
     * Get Back URL
     *
     * @return string
     */
    public function getBackUrl(){
        return $this->getUrl("vendors/message_all");
    }


}
