<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsLanguage\Model\Source;

class Locale extends \Magento\Config\Model\Config\Source\Locale
{

    /**
     * (non-PHPdoc)
     * @see \Magento\Config\Model\Config\Source\Locale::toOptionArray()
     */
    public function toOptionArray(){
        $options = parent::toOptionArray();
        $defaultLangOtp = ['value' => '', 'label' => __("Default")];
        array_unshift($options, $defaultLangOtp);

        return $options;
    }
}
