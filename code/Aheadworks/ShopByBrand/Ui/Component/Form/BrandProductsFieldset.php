<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Ui\Component\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Framework\App\RequestInterface;

class BrandProductsFieldset extends Fieldset
{
    private $request;

    /**
     * @param ContextInterface $context
     * @param RequestInterface $request
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct($context, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        if (!$this->request->getParam('brand_id', false)) {
            $config = $this->getData('config');
            $config['visible'] = false;
            $this->setData('config', $config);
        }

        parent::prepare();
    }
}
