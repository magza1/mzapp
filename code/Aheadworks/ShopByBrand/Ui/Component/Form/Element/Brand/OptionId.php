<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Ui\Component\Form\Element\Brand;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Model\Brand\Source\OptionId as OptionIdSource;
use Aheadworks\ShopByBrand\Model\Config;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Select;

/**
 * Class OptionId
 * @package Aheadworks\ShopByBrand\Ui\Component\Form\Element\Brand
 */
class OptionId extends Select
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepository;

    /**
     * @param ContextInterface $context
     * @param Config $config
     * @param BrandRepositoryInterface $brandRepository
     * @param OptionIdSource $optionIdSource
     * @param null $options
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        Config $config,
        BrandRepositoryInterface $brandRepository,
        OptionIdSource $optionIdSource,
        $options = null,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $options,
            $components,
            $data
        );
        $this->config = $config;
        $this->brandRepository = $brandRepository;
        $this->initOptions($optionIdSource);
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        $brandId = $this->getBrandId();
        if ($brandId) {
            $brandAttributeCode = $this->brandRepository->get($brandId)
                ->getAttributeCode();
            $configAttributeCode = $this->config->getBrandProductAttributeCode();
            if ($brandAttributeCode != $configAttributeCode) {
                $config['disabled'] = true;
            }
        }
        $this->setData('config', $config);
        parent::prepare();
    }

    /**
     * Init options
     *
     * @param OptionIdSource $optionIdSource
     * @return void
     */
    private function initOptions(OptionIdSource $optionIdSource)
    {
        $brandId = $this->getBrandId();
        if ($brandId) {
            $brand = $this->brandRepository->get($brandId);
            $attributeCode = $brand->getAttributeCode();
            $optionIdSource->setCurrentOptionId($brand->getOptionId());
        } else {
            $attributeCode = $this->config->getBrandProductAttributeCode();
        }
        $this->options = $optionIdSource->setAttributeCode($attributeCode)
            ->toOptionArray();
    }

    /**
     * Get current brand ID
     *
     * @return string|null
     */
    private function getBrandId()
    {
        return $this->getContext()->getRequestParam(
            $this->getContext()->getDataProvider()->getRequestFieldName(),
            null
        );
    }
}
