<?php
/**
 * Catalog price rules
 *
 * @author      Vnecoms Team <core@vnecoms.com>
 */
namespace Vnecoms\VendorsProduct\Block\Vendors;

class Product extends \Vnecoms\Vendors\Block\Vendors\Widget\Container
{
    
    /**
     * @var \Magento\Catalog\Model\Product\TypeFactory
     */
    protected $_typeFactory;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;
    
    /**
     * @var \Vnecoms\VendorsProduct\Helper\Data
     */
    protected $_productHelper;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Vnecoms_VendorsProduct';
        $this->_controller = 'vendors_product';
        $this->_headerText = __('Manage Products');
        $this->_addButtonLabel = __('Add Product');
        
        parent::_construct();
        //$this->removeButton('add');
    }
    
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Catalog\Model\Product\TypeFactory $typeFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Catalog\Model\Product\TypeFactory $typeFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Vnecoms\VendorsProduct\Helper\Data $productHelper,
        array $data = []
    ) {
        $this->_productFactory  = $productFactory;
        $this->_typeFactory     = $typeFactory;
        $this->_productHelper   = $productHelper;
        
        parent::__construct($context, $data);
        $this->removeButton('add');
    }
    
    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new_product',
            'label' => __('Add Product'),
            'class' => 'btn-primary btn-sm',
            'button_class' => 'fa fa-plus-circle',
            'class_name' => 'Vnecoms\Vendors\Block\Vendors\Widget\Button\SplitButton',
            'options' => $this->_getAddProductButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps, 0, 0, 'toolbar');
    
        return parent::_prepareLayout();
    }
    
    /**
     * Retrieve options for 'Add Product' split button
     *
     * @return array
     */
    protected function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];
        $types = $this->_typeFactory->create()->getTypes();
        uasort(
            $types,
            function ($elementOne, $elementTwo) {
                return ($elementOne['sort_order'] < $elementTwo['sort_order']) ? -1 : 1;
            }
        );

        foreach ($types as $typeId => $type) {
            if (in_array($typeId, $this->_productHelper->getProductTypeRestriction())) {
                continue;
            }
            
            $splitButtonOptions[$typeId] = [
                'label' => __($type['label']),
                'onclick' => "setLocation('" . $this->_getProductCreateUrl($typeId) . "')",
                'default' => \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE == $typeId,
            ];
        }
    
        return $splitButtonOptions;
    }
    
    /**
     * Retrieve product create url by specified product type
     *
     * @param string $type
     * @return string
     */
    protected function _getProductCreateUrl($type)
    {
        return $this->getUrl(
            'catalog/product/new',
            ['set' => $this->_productFactory->create()->getDefaultAttributeSetId(), 'type' => $type]
        );
    }
    
    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
