<?php
namespace Vnecoms\VendorsPage\Block\System\Config\Form\Field;


class Editor extends \Vnecoms\VendorsConfig\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
    
    /**
     * 
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context,$data);
    }
    
    
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        //$wysiwygConfig = $this->_wysiwygConfig->getConfig();
        $wysiwygConfig = new \Magento\Framework\DataObject([
            'enabled' => true,
            'theme' => 'simple',
            'theme_advanced_buttons2' => '',
            'theme_advanced_buttons3' => '',
            'theme_advanced_buttons4' => '',
            'plugins' => '',
        ]);
        

        $element->setTheme('simple');
        $element->setWysiwyg(true);
        $element->setConfig($wysiwygConfig);
        return parent::_getElementHtml($element);
    }
}
