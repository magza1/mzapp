<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Ui\Component\Listing\Column;

use Aheadworks\ShopByBrand\Model\Url;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Thumbnail
 * @package Aheadworks\ShopByBrand\Ui\Component\Listing\Column
 */
class Thumbnail extends Column
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'thumbnail';

    /**
     * @var Url
     */
    private $url;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Url $url
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Url $url,
        array $components = [],
        array $data = []
    ) {
        $this->url = $url;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $index = $this->getName();
            $config = $this->getConfiguration();
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$index . '_src'] = $this->url->getLogoUrl($item[$index], 'thumbnail');
                $item[$index . '_alt'] = $item[$config['altField']];
            }
        }
        return $dataSource;
    }
}
