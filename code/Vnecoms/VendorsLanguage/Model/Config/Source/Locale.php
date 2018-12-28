<?php
namespace Vnecoms\VendorsLanguage\Model\Config\Source;

class Locale extends \Magento\Config\Model\Config\Source\Locale
{
    /**
     * (non-PHPdoc)
     * @see \Magento\Config\Model\Config\Source\Locale::toOptionArray()
     */
    public function toOptionArray(){
        $options = parent::toOptionArray();
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $object_manager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $localeNotAllow = $config->getValue("vendors/design/locale_restriction");
        $localeNotAllow = explode(",",$localeNotAllow);
        $newOptions = [];
        $defaultLangOtp = ['value' => '', 'label' => __("Default")];
        array_unshift($options, $defaultLangOtp);
        foreach ($options as $option){
            if(in_array($option['value'],$localeNotAllow)) continue;
            $newOptions[] = $option;
        }
        return $newOptions;
    }
}
