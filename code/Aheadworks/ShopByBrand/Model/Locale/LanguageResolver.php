<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Locale;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class LanguageResolver
 * @package Aheadworks\ShopByBrand\Model\Locale
 */
class LanguageResolver
{
    /**
     * Xml path to locale code config value
     */
    const XML_PATH_LOCALE = 'general/locale/code';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get primary language code for store
     *
     * @param int $storeId
     * @return string
     */
    public function get($storeId)
    {
        $storeLocale = $this->scopeConfig->getValue(
            self::XML_PATH_LOCALE,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
        return \Locale::getPrimaryLanguage($storeLocale);
    }
}
