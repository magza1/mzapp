<?php
namespace Magecomp\Mobilelogin\Model\ResourceModel\Regotpmodel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magecomp\Mobilelogin\Model\Regotpmodel', 'Magecomp\Mobilelogin\Model\ResourceModel\Regotpmodel');
    }

    
}
