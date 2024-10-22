<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class AddTotalCustomsSurchargeToOrder implements ObserverInterface
{
    /**
     * @var PriceCurrencyInterface $priceCurrency
     */
    private PriceCurrencyInterface $priceCurrency;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): self
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        if ($quote->isVirtual()) {
            return $this;
        }

        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        $shippingAddress = $quote->getShippingAddress();
        $baseAmount = $shippingAddress->getData('base_shipping_surcharge_amount') ?: 0;
        $amount = $this->priceCurrency->convert($baseAmount, $order->getStore());
        if ($baseAmount > 0) {
            $order->addData([
                'shipping_surcharge_amount' => $amount,
                'base_shipping_surcharge_amount' => $baseAmount,
            ]);

            $order->setShippingAmount($order->getShippingAmount() + $amount);
            $order->setBaseShippingAmount($order->getBaseShippingAmount() + $baseAmount);
        }

        return $this;
    }
}
