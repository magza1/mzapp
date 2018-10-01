<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\Vendors\Block\Vendors\Page;

/**
 * Vendor footer block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Footer extends \Vnecoms\Vendors\Block\Vendors\AbstractBlock
{
    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    protected $_moduleList;
    
    /**
     * @var \Vnecoms\Vendors\Helper\Data
     */
    protected $_helper;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Vnecoms\Vendors\Model\UrlInterface $url,
        \Vnecoms\Vendors\Helper\Data $vendorHelper,
        array $data = []
    ) {
        $this->_helper = $vendorHelper;
        parent::__construct($context, $url, $data);
    }
    
    /**
     * Get current version of the extension.
     * @return string
     */
    public function getVersion()
    {
        if (!$this->_moduleList) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $this->_moduleList = $om->create("Magento\Framework\Module\ModuleList");
        }
        $extensionInfo = $this->_moduleList->getOne('Vnecoms_Vendors');
        return $extensionInfo['setup_version'];
    }
    
    /**
     * Get footer text
     *
     * @return string
     */
    public function getFooterText()
    {
        return $this->_helper->getFooterText();
    }
}
