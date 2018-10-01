<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsConfig\Model\Config\Structure\Element;

class Section extends \Magento\Config\Model\Config\Structure\Element\Section
{
    /**
     * Check whether section is allowed for current user
     *
     * @return bool
     */
    public function isAllowed()
    {
        return true;
    }
}
