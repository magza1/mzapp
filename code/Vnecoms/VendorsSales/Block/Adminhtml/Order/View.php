<?php
/**
 * @category    Magento
 * @package     Magento_Sales
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsSales\Block\Adminhtml\Order;

/**
 * Adminhtml sales order view
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends \Magento\Sales\Block\Adminhtml\Order\View
{


    /**
     * Constructor
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'adminhtml_order';
        $this->_mode = 'view';

        \Magento\Backend\Block\Widget\Form\Container::_construct();

        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('save');
        $this->setId('sales_order_view');
        $order = $this->getOrder();

        if (!$order) {
            return;
        }

        if ($this->_isAllowedAction('Magento_Sales::actions_edit') && $order->canEdit()) {
            $onclickJs = 'jQuery(\'#order_edit\').orderEditDialog({message: \''
                . $this->getEditMessage($order) . '\', url: \'' . $this->getEditUrl()
                . '\'}).orderEditDialog(\'showDialog\');';

            $this->buttonList->add(
                'order_edit',
                [
                    'label' => __('Edit'),
                    'class' => 'edit primary',
                    'onclick' => $onclickJs,
                    'data_attribute' => [
                        'mage-init' => '{"orderEditDialog":{}}',
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::cancel') && $order->canCancel()) {
            $this->buttonList->add(
                'order_cancel',
                [
                    'label' => __('Cancel'),
                    'class' => 'cancel',
                    'id' => 'order-view-cancel-button',
                    'data_attribute' => [
                        'url' => $this->getCancelUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::emails') && !$order->isCanceled()) {
            $message = __('Are you sure you want to send an order email to customer?');
            $this->addButton(
                'send_notification',
                [
                    'label' => __('Send Email'),
                    'class' => 'send-email',
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getEmailUrl()}')"
                ]
            );
        }


        /*
        if ($this->_isAllowedAction('Magento_Sales::creditmemo') && $order->canCreditmemo()) {
            $message = __(
                'This will create an offline refund. ' .
                'To create an online refund, open an invoice and create credit memo for it. Do you want to continue?'
            );
            $onClick = "setLocation('{$this->getCreditmemoUrl()}')";
            if ($order->getPayment()->getMethodInstance()->isGateway()) {
                $onClick = "confirmSetLocation('{$message}', '{$this->getCreditmemoUrl()}')";
            }
            $this->buttonList->add(
                'order_creditmemo',
                ['label' => __('Credit Memo'), 'onclick' => $onClick, 'class' => 'credit-memo']
            );
        }*/

        // invoice action intentionally
        if ($this->_isAllowedAction('Magento_Sales::invoice') && $order->canVoidPayment()) {
            $message = __('Are you sure you want to void the payment?');
            $this->addButton(
                'void_payment',
                [
                    'label' => __('Void'),
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getVoidPaymentUrl()}')"
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::hold') && $order->canHold()) {
            $this->buttonList->add(
                'order_hold',
                [
                    'label' => __('Hold'),
                    'class' => __('hold'),
                    'id' => 'order-view-hold-button',
                    'data_attribute' => [
                        'url' => $this->getHoldUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::unhold') && $order->canUnhold()) {
            $this->buttonList->add(
                'order_unhold',
                [
                    'label' => __('Unhold'),
                    'class' => __('unhold'),
                    'id' => 'order-view-unhold-button',
                    'data_attribute' => [
                        'url' => $this->getUnHoldUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::review_payment')) {
            if ($order->canReviewPayment()) {
                $message = __('Are you sure you want to accept this payment?');
                $this->buttonList->add(
                    'accept_payment',
                    [
                        'label' => __('Accept Payment'),
                        'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('accept')}')"
                    ]
                );
                $message = __('Are you sure you want to deny this payment?');
                $this->buttonList->add(
                    'deny_payment',
                    [
                        'label' => __('Deny Payment'),
                        'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('deny')}')"
                    ]
                );
            }
            if ($order->canFetchPaymentReviewUpdate()) {
                $this->buttonList->add(
                    'get_review_payment_update',
                    [
                        'label' => __('Get Payment Update'),
                        'onclick' => 'setLocation(\'' . $this->getReviewPaymentUrl('update') . '\')'
                    ]
                );
            }
        }


        if ($this->_isAllowedAction('Magento_Sales::invoice') && $order->canInvoice()) {
            $_label = $order->getForcedShipmentWithInvoice() ? __('Invoice and Ship') : __('Invoice');
            $this->buttonList->add(
                'order_invoice',
                [
                    'label' => $_label,
                    'onclick' => 'setLocation(\'' . $this->getInvoiceUrl() . '\')',
                    'class' => 'invoice'
                ]
            );
        }

        /*
                if ($this->_isAllowedAction(
                        'Magento_Sales::ship'
                    ) && $order->canShip() && !$order->getForcedShipmentWithInvoice()
                ) {
                    $this->buttonList->add(
                        'order_ship',
                        [
                            'label' => __('Ship'),
                            'onclick' => 'setLocation(\'' . $this->getShipUrl() . '\')',
                            'class' => 'ship'
                        ]
                    );
        } */

        if ($this->_isAllowedAction(
            'Magento_Sales::reorder'
        ) && $this->_reorderHelper->isAllowed(
            $order->getStore()
        ) && $order->canReorderIgnoreSalable()
        ) {
            $this->buttonList->add(
                'order_reorder',
                [
                    'label' => __('Reorder'),
                    'onclick' => 'setLocation(\'' . $this->getReorderUrl() . '\')',
                    'class' => 'reorder'
                ]
            );
        }
    }
}
