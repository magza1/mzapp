<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Block\Adminhtml\Page\Menu;

use Aheadworks\ShopByBrand\Block\Adminhtml\Page\Menu;
use Aheadworks\ShopByBrand\Model\Config;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

/**
 * Class BrandAttribute
 * @package Aheadworks\ShopByBrand\Block\Adminhtml\Page\Menu
 */
class BrandAttribute extends Item
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @param Context $context
     * @param Config $config
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * @inheritdoc
     */
    protected function prepareLinkAttributes()
    {
        $brandAttributeCode = $this->config->getBrandProductAttributeCode();
        if ($brandAttributeCode) {
            $brandAttribute = $this->productAttributeRepository->get($brandAttributeCode);
            $linkAttributes = is_array($this->getLinkAttributes())
                ? $this->getLinkAttributes()
                : [];
            $linkAttributes['href'] = $this->getUrl(
                $this->getPath(),
                ['attribute_id' => $brandAttribute->getAttributeId()]
            );
            $this->setLinkAttributes($linkAttributes);
        }
        parent::prepareLinkAttributes();
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->config->getBrandProductAttributeCode()) {
            return '';
        }
        return parent::_toHtml();
    }
}
