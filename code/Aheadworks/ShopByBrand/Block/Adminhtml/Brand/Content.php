<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Block\Adminhtml\Brand;

use Aheadworks\ShopByBrand\Api\Data\BrandContentInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Ui\Component\Wysiwyg\ConfigInterface as WysiwygConfig;

/**
 * Class Content
 * @package Aheadworks\ShopByBrand\Block\Adminhtml\Brand
 */
class Content extends \Magento\Backend\Block\Template
{
    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var WysiwygConfig
     */
    private $wysiwygConfig;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_ShopByBrand::brand/content.phtml';

    /**
     * @param Context $context
     * @param SystemStore $systemStore
     * @param Registry $coreRegistry
     * @param WysiwygConfig $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        SystemStore $systemStore,
        Registry $coreRegistry,
        WysiwygConfig $wysiwygConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->systemStore = $systemStore;
        $this->coreRegistry = $coreRegistry;
        $this->wysiwygConfig = $wysiwygConfig;
    }

    /**
     * Get stores options
     *
     * @return array
     */
    public function getStoresOptions()
    {
        return $this->systemStore->getStoreValuesForForm(false, true);
    }

    /**
     * Get content
     *
     * @return array
     */
    public function getContent()
    {
        $content = $this->coreRegistry->registry('aw_brand_content') ? : [];
        if (count($content) == 0) {
            $content[] = [
                BrandContentInterface::STORE_ID  => 0,
                BrandContentInterface::META_TITLE => '',
                BrandContentInterface::META_DESCRIPTION => '',
                BrandContentInterface::DESCRIPTION => ''
            ];
        }
        return $content;
    }

    /**
     * Get wysiwyg config data
     *
     * @return array
     */
    public function getWysiwygConfig()
    {
        return array_merge(
            $this->wysiwygConfig->getConfig()->toArray(),
            ['height' => '300px']
        );
    }
}
