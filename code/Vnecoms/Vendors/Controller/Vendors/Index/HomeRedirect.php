<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\Vendors\Controller\Vendors\Index;

class HomeRedirect extends \Vnecoms\Vendors\Controller\Vendors\Action
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_redirect('dashboard');
    }
}
