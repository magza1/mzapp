<?php

namespace Vnecoms\Vendors\Model\Config;

use Magento\Framework\App\ObjectManager;

class Manager extends \Magento\Ui\Model\Manager
{
    /**
     * Prepare the initialization data of UI components
     *
     * @param string $name
     * @return ManagerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareData($name)
    {
		if ($name === null || $this->hasData($name)) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase("Invalid UI Component element name: '%1'", [$name])
            );
        }
        $this->componentsPool = $this->arrayObjectFactory->create();

        $state = ObjectManager::getInstance()->get('Magento\Framework\App\State');
        $cacheID = $state->getAreaCode().'_'.static::CACHE_ID . '_' . $name;
        $cachedPool = $this->cache->load($cacheID);
        if ($cachedPool === false) {
            $this->prepare($name);
            $this->cache->save($this->componentsPool->serialize(), $cacheID);
        } else {
            $this->componentsPool->unserialize($cachedPool);
        }
        $this->componentsData->offsetSet($name, $this->componentsPool);
        $this->componentsData->offsetSet($name, $this->evaluateComponentArguments($this->getData($name)));

        return $this;
    }

}
