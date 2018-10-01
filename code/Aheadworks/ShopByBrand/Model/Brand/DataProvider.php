<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand;

use Aheadworks\ShopByBrand\Model\ResourceModel\Brand\Collection;
use Aheadworks\ShopByBrand\Model\ResourceModel\Brand\CollectionFactory;
use Aheadworks\ShopByBrand\Model\Brand;
use Aheadworks\ShopByBrand\Model\Url;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 * @package Aheadworks\ShopByBrand\Model\Brand
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var FileInfo
     */
    private $fileInfo;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $brandCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param Url $url
     * @param FileInfo $fileInfo
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $brandCollectionFactory,
        DataPersistorInterface $dataPersistor,
        Url $url,
        FileInfo $fileInfo,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $brandCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->fileInfo = $fileInfo;
        $this->url = $url;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var $brand Brand */
        foreach ($items as $brand) {
            $this->loadedData[$brand->getBrandId()] = $this->prepareFormData($brand->getData());
        }

        $data = $this->dataPersistor->get('aw_brand');
        if (!empty($data)) {
            $brand = $this->collection->getNewEmptyItem();
            $brand->setData($data);
            $this->loadedData[$brand->getBrandId()] = $this->prepareFormData($brand->getData());
            $this->dataPersistor->clear('aw_brand');
        }

        return $this->loadedData;
    }

    /**
     * Prepare form data
     *
     * @param array $data
     * @return array
     */
    private function prepareFormData($data)
    {
        if (isset($data['logo'])) {
            $imageName = $data['logo'];
            unset($data['logo']);
            if ($this->fileInfo->isExist($imageName)) {
                $stat = $this->fileInfo->getStat($imageName);
                $data['logo'] = [
                    [
                        'name' => $imageName,
                        'url' => $this->url->getLogoUrl($imageName),
                        'size' => isset($stat) ? $stat['size'] : 0,
                        'type' => $this->fileInfo->getMimeType($imageName)
                    ]
                ];
            }
        }
        return $data;
    }
}
