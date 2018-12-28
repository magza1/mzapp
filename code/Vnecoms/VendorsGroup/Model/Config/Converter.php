<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsGroup\Model\Config;

use Magento\Framework\Module\Manager;
use Magento\Framework\App\Utility\Classes;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param Manager $moduleManager
     */
    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];
        /** @var \DOMNodeList $groups */
        $groups = $source->getElementsByTagName('group');
        /** @var \DOMNode $entityConfig */
        foreach ($groups as $groupConfig) {
            $groupData = [];
            $attributes = $groupConfig->attributes;
            foreach ($attributes as $attrName => $attr) {
                $groupData[$attrName] = $attr->nodeValue;
            }
            $label = '';
            $fields = [];
            foreach ($groupConfig->childNodes as $node) {
                if ($node->nodeName == 'label') {
                    $label = $node->nodeValue;
                } elseif ($node->nodeName == 'field') {
                    $field = [];
                    $attrs = $node->attributes;
                    foreach ($attrs as $fieldName => $fieldConfig) {
                        $field[$fieldName] = $fieldConfig->nodeValue;
                    }
                    foreach ($node->childNodes as $n) {
                        if ($n->nodeType == XML_ELEMENT_NODE) {
                            $field[$n->nodeName] = $n->nodeValue;
                        }
                    }
                    $fields[] = $field;
                }
            }
            $groupData['label'] = $label;
            $groupData['fields'] = $fields;
            $output[] = $groupData;
        }

        return $output;
    }
}
