<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\Credit\Model\Quote;

use Magento\Tax\Model\Calculation;

class Credit extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Address rate request
     *
     * Request object contain:
     *  country_id (->getCountryId())
     *  region_id (->getRegionId())
     *  postcode (->getPostcode())
     *  customer_class_id (->getCustomerClassId())
     *  store (->getStore())
     *
     * @var \Magento\Framework\DataObject
     */
    private $addressRateRequest = null;
    
    
    /**
     * Tax calculation tool
     *
     * @var Calculation
     */
    protected $calculationTool;
    
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Calculation $calculationTool,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->setCode('credit');
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->calculationTool = $calculationTool;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Collect address discount amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

//         $taxRateRequest = $this->getAddressRateRequest()->setProductClassId(
//             $this->taxClassManagement->getTaxClassId($item->getTaxClassKey())
//         );
//         $rate = $this->calculationTool->getRate($taxRateRequest);
        
        
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }
        
        $store = $this->storeManager->getStore();
        $address = $shippingAssignment->getShipping()->getAddress();
        
//         $maxAmount = $address->getSubtotalWithDiscount() + $address->getShippingAmount();
//         $usedCredit = min(abs($quote->getData('credit_amount')), $maxAmount);
//         $rate = $this->priceCurrency->convert(1);
//         $baseUsedCredit = $usedCredit / $rate;

        $maxAmount = $address->getBaseSubtotalWithDiscount() + $address->getBaseShippingAmount();
        $baseUsedCredit = min(abs($quote->getData('base_credit_amount')), $maxAmount);
        $usedCredit = $this->priceCurrency->convert($baseUsedCredit);

        /*Reset the credit amount from item*/
        if(!$usedCredit) {
            foreach($items as $item){
                if ($item->getParentItem()) {
                    continue;
                }
                
                $item->setCreditAmount(0);
                $item->setBaseCreditAmount(0);
                //$item->save();
            }
            return $this;
        }
        
        /*Round the used credit*/
        $creditRate = $baseUsedCredit * 1.0 / $address->getBaseSubtotal();
        
        $calculatedCreditItems = [];
        foreach($items as $item){
            if ($item->getParentItem()) {
                continue;
            }
            $rate = $creditRate * $item->getQty();
            $baseCreditAmount = $item->getBasePrice() * $rate;
            $creditAmount = $this->priceCurrency->convert($baseCreditAmount);
            $calculatedCreditItems[] = ['item'=>$item, 'credit_amount'=> $creditAmount, 'base_credit_amount'=>$baseCreditAmount];
        }
        
        $this->applyCreditToItems($calculatedCreditItems,$usedCredit, $baseUsedCredit);
        
        
        $quote->setCreditAmount(-$usedCredit)->setBaseCreditAmount(-$baseUsedCredit);
        
        $total->addTotalAmount($this->getCode(), -$usedCredit);
        $total->addBaseTotalAmount($this->getCode(), -$baseUsedCredit);
        return $this;
    }

    /**
     * 
     * @param array $calculatedCreditItems
     * @param float $usedCredit
     * @param float $baseUsedCredit
     */
    public function applyCreditToItems($calculatedCreditItems=[],$usedCredit, $baseUsedCredit){
        $creditTotal = 0;
        $baseCreditTotal = 0;
        $lastCreditItem = null;
        $itemCount = sizeof($calculatedCreditItems);
        $count = 0;
        foreach($calculatedCreditItems as $creditItem){
            $count ++;
            if($count == $itemCount){
                /*Last Item*/
                $creditAmount = $usedCredit - $creditTotal;
                $baseCreditAmount = $baseUsedCredit - $baseCreditTotal;
                
            }else{
                $creditAmount = $creditItem['credit_amount'];
                $baseCreditAmount = $creditItem['base_credit_amount'];
            }
            $item = $creditItem['item'];
            $item->setCreditAmount($creditAmount);
            $item->setBaseCreditAmount($baseCreditAmount);
            //$item->save();
            
            $creditTotal += $creditAmount;
            $baseCreditTotal += $baseCreditAmount;
        }
        
    }

    /**
     * Get address rate request
     *
     * Request object contain:
     *  country_id (->getCountryId())
     *  region_id (->getRegionId())
     *  postcode (->getPostcode())
     *  customer_class_id (->getCustomerClassId())
     *  store (->getStore())
     *
     * @return \Magento\Framework\DataObject
     */
    protected function getAddressRateRequest()
    {
        if (null == $this->addressRateRequest) {
            $this->addressRateRequest = $this->calculationTool->getRateRequest(
                $this->shippingAddress,
                $this->billingAddress,
                $this->customerTaxClassId,
                $this->storeId,
                $this->customerId
            );
        }
        return $this->addressRateRequest;
    }
    
    
    /**
     * Add discount total information to address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $amount = $total->getCreditAmount();
        if ($amount != 0) {
            $result = [
                'code' => $this->getCode(),
                'title' => __('Store Credit (%1)',$amount),
                'value' => $amount
            ];
        }
        return $result;
    }
}
