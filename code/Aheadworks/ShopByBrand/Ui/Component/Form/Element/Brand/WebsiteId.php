<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Ui\Component\Form\Element\Brand;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Api\WebsiteManagementInterface;
use Magento\Ui\Component\Form\Element\MultiSelect;

/**
 * Class WebsiteId
 * @package Aheadworks\ShopByBrand\Ui\Component\Form\Element\Brand
 */
class WebsiteId extends MultiSelect
{
    /**
     * @var WebsiteManagementInterface
     */
    private $websiteManagement;

    /**
     * @param ContextInterface $context
     * @param WebsiteManagementInterface $websiteManagement
     * @param array|null $options
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        WebsiteManagementInterface $websiteManagement,
        $options = null,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $options, $components, $data);
        $this->websiteManagement = $websiteManagement;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if ($this->websiteManagement->getCount() == 1) {
            $config['componentDisabled'] = true;
        }
        $this->setData('config', $config);
        parent::prepare();
    }
}
