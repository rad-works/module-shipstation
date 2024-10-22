<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Block\Adminhtml\Order\Totals;

use Magento\Sales\Block\Adminhtml\Totals;

class CustomsSurcharge extends Totals
{
    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Adminhtml\Totals $parent */
        $parent = $this->getParentBlock();
        $order = $parent->getOrder();
        $amount = $order->getData('shipping_surcharge_amount');
        $baseAmount = $order->getData('base_shipping_surcharge_amount');
        if ($baseAmount) {
            $total = $parent->getTotal('shipping');
            $label = $total->getLabel();
            $total->setLabel($label . ' & ' . __('Customs Surcharge(+%1)', $order->formatPriceTxt($amount)));
        }

        return $this;
    }
}