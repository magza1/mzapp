<?php
namespace Vnecoms\VendorsCredit\Block\Adminhtml\Sales\Order\Items\Column;

/**
 * Sales Order items commission column renderer
 */
class Commission extends \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
{
    /**
     * Add new column to the render
     * @see \Magento\Framework\View\Element\AbstractBlock::_prepareLayout()
     */
    protected function _prepareLayout(){
        $parentBlock = $this->getParentBlock();
        foreach($this->getLayout()->getChildNames($parentBlock->getNameInLayout()) as $blockName){
            $block = $this->getLayout()->getBlock($blockName);
            if($block instanceof \Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer){
                $columns = $block->getColumns();
                $columns['commission'] = 'col-commission';
                $block->setData('columns', $columns);
            }
        }
    }
    
    /**
     * Get item commission
     * 
     * @return string
     */
    public function getCommissionAmount(){
        $item = $this->getItem();
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $invoiceItemIds = [$item->getId()];
        if($item instanceof \Magento\Sales\Model\Order\Item){
            $invoiceItemIds = [];
            $invoiceItemCollection = $om->create('Magento\Sales\Model\ResourceModel\Order\Invoice\Item\Collection');
            $invoiceItemCollection->addFieldToFilter('order_item_id', $item->getId());
            $invoiceItemIds = $invoiceItemCollection->getAllIds();
        }
        
        $invoiceItems =[];
        foreach($invoiceItemIds as $invoiceItemId){
            $invoiceItems[] = 'invoice_item|'.$invoiceItemId;
        }
        
        $creditTransactionCollection = $om->create('Vnecoms\Credit\Model\ResourceModel\Credit\Transaction\Collection');
        $creditTransactionCollection->addFieldToFilter('additional_info',['in' => $invoiceItems]);
        $amount = 0;
        foreach($creditTransactionCollection as $trans){
            $amount += $trans->getAmount();
        }
        
        return abs($amount);
    }
    
    /**
     * Show two price
     * 
     * @return boolean
     */
    public function showTwoPrice(){
        return $this->getOrder()->getOrderCurrencyCode() != $this->getOrder()->getBaseCurrencyCode();
    }
    
    /**
     * @param float $basePrice
     * @return string
     */
    public function formatCommission($basePrice){
        $order = $this->getOrder();
        $commission = $order->getBaseCurrency()->convert($basePrice, $order->getOrderCurrencyCode());
        return $order->getOrderCurrency()->formatTxt($commission);
    }
    
    /**
     * @param float $basePrice
     * @return string
     */
    public function formatBaseCommission($basePrice){
        return $this->getOrder()->getBaseCurrency()->formatTxt($basePrice);
    }
}
