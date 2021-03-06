<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Storelocator
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Storelocator\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magestore\Storelocator\Model\Config\Source\OrderTypeStore;

/**
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class loadstorequick extends \Magestore\Storelocator\Controller\Index
{
    /**
     * Default current page.
     */
    const DEFAULT_CURRENT_PAGINATION = 1;

    /**
     * Default range pagination.
     */
    const DEFAULT_RANGE_PAGINATION = 3;

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
	
	
		/** @var \Magestore\Storelocator\Model\ResourceModel\Store\Collection $collection */
		$collection = $this->_filterStoreCollection($this->_storeCollectionFactory->create());

        $product_id = $this->getRequest()->getParam('pid');
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
        
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $pager = $resultPage->getLayout()->createBlock(
            'Magestore\Storelocator\Block\ListStore\Pagination',
            'storelocator.pager',
            [
                'collection' => $collection,
                'data' => ['range' => self::DEFAULT_RANGE_PAGINATION],
            ]
        );
			
        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $response->setHeader('Content-type', 'text/plain');

        $response->setContents(
            $this->_jsonHelper->jsonEncode(
                [
                    'storesjson' => $collection->prepareJson(),
                    'pagination' => $pager->toHtml(),
                    'num_store' => $collection->getSize(),
                    'product_name' => $product->getName(),
                    'product_url' => $product->getProductUrl()
                ]
            )
        );
	
        return $response;
    }

    /**
     * filter store.
     *
     * @param \Magestore\Storelocator\Model\ResourceModel\Store\Collection $collection
     *
     * @return \Magestore\Storelocator\Model\ResourceModel\Store\Collection
     */
    protected function _filterStoreCollection(
        \Magestore\Storelocator\Model\ResourceModel\Store\Collection $collection
    ) {
		
		$product_id = $this->getRequest()->getParam('pid');
        $product_id = trim($product_id,"/");
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
		
		//exit;
		
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		//$product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');//get current product
		
		//if($pcount > '1'){
		$bn = $product->getBn();
		$pag = $product->getPag();
		$cns = $product->getCns();
		$cbid = $product->getCbid();
		$pg = $product->getPg();
		
		
	
		$query = "SELECT magestore_storelocator_store.storelocator_id FROM magestore_extra,magestore_storelocator_store WHERE(((MATCH(magestore_extra.sbn) AGAINST('".$bn."' IN BOOLEAN MODE))AND(MATCH(magestore_extra.scns)AGAINST('".$cns."' IN BOOLEAN MODE))OR(MATCH(magestore_extra.scbid) AGAINST('".$cbid."' IN BOOLEAN MODE))) AND (magestore_extra.sag>='".$pag."')OR(MATCH(magestore_extra.spg) AGAINST('".$pg."' IN BOOLEAN MODE)))AND magestore_storelocator_store.sgn=magestore_extra.sgn";
		$result3 = $connection->fetchAll($query);
		$store_id;
		$last = count($result3);
		$i = 1;
		
		
		
		foreach($result3 as $result){
			
			if($i == '1'){
				$store_id = $result['storelocator_id'].',';
			}if($i == $last){
				$store_id .=  $result['storelocator_id'];
			}else{
				$store_id .= $result['storelocator_id'].',';
			}
			
			//$store_id .= $result['storelocator_id'].',';
			$i++;
		}
		if(count($result3) > '0'){
			$srorid = explode(",",$store_id);
		}else{
			$srorid = "";
		}	

       // var_dump($srorid);exit;

		$collection->addFieldToFilter('storelocator_id',array('in' => array($srorid)));
				
		
        $collection->addFieldToSelect([
			'main_storelocator_id' => "storelocator_id",
            'store_name',
            'mobile_phone',
            'message',
            'phone',
            'address',
            'latitude',
            'longitude',
            'marker_icon',
            'zoom_level',
            'rewrite_request_path',
            'sgn',
        ]);

        $curPage = $this->getRequest()->getParam('curPage', self::DEFAULT_CURRENT_PAGINATION);
        $collection->setPageSize($this->_systemConfig->getPainationSize())->setCurPage($curPage);

        /*
         * Filter store enabled
         */
        $collection->addFieldToFilter('status', \Magestore\Storelocator\Model\Status::STATUS_ENABLED);

         /*
         * filter by radius
         */
        if ($radius = $this->getRequest()->getParam('radius')) {
            $latitude = $this->getRequest()->getParam('latitude');
            $longitude = $this->getRequest()->getParam('longitude');
            $collection->addLatLngToFilterDistance($latitude, $longitude, $radius);
        }

        /*
         * filter by tags
         */
        $tagIds = $this->getRequest()->getParam('tagIds');
        if (!empty($tagIds)) {
            $collection->addTagsToFilter($tagIds);
        }



        /*
         * filter by store information
         */

        if ($countryId = $this->getRequest()->getParam('countryId')) {
            $collection->addFieldToFilter('country_id', $countryId);
        }

        if ($storeName = $this->getRequest()->getParam('storeName')) {
            $collection->addFieldToFilter('store_name', ['like' => "%$storeName%"]);
        }

        if ($state = $this->getRequest()->getParam('state')) {
            $collection->addFieldToFilter('state', ['like' => "%$state%"]);
        }

        if ($city = $this->getRequest()->getParam('city')) {
            $collection->addFieldToFilter('city', ['like' => "%$city%"]);
        }

        if ($zipcode = $this->getRequest()->getParam('zipcode')) {
            $collection->addFieldToFilter('zipcode', ['like' => "%$zipcode%"]);
        }


       
        // Set sort type for list store
        switch ($this->_systemConfig->getSortStoreType()) {
            case OrderTypeStore::SORT_BY_ALPHABETICAL:
                $collection->setOrder('store_name', \Magento\Framework\Data\Collection\AbstractDb::SORT_ORDER_ASC);
                break;
       
            case OrderTypeStore::SORT_BY_DISTANCE:
                if ($radius)
                    $collection->setOrder('distance', \Magento\Framework\Data\Collection\AbstractDb::SORT_ORDER_ASC);
                else
                    $collection->setOrder('sort_order', \Magento\Framework\Data\Collection\AbstractDb::SORT_ORDER_ASC);
                break;
            default:
                $collection->setOrder('sort_order', \Magento\Framework\Data\Collection\AbstractDb::SORT_ORDER_DESC);
        }
       
 
        // Allow load base image for each store
        $collection->setLoadBaseImage(true);
				

    	
        $collection->getSelect()->joinLeft(
       ['prc_table'=>$collection->getTable('magestore_prc')],
        'main_table.sgn = prc_table.psgn AND prc_table.pid = "'.$product_id.'"',
       ['prc_message'=> 'prc_table.prc']);//->where('prc_table.pid = "'.$product_id.'"'); 


        return $collection;
    }
}
