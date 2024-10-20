<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class AddTotalCustomItemToCart implements ObserverInterface
{
    /**
     * Set DropDay export status flag
     *
     * @param Observer $observer
     * @return $this
     * @TODO Implement handling
     */
    public function execute(Observer $observer): self
    {
        /** @var Order $order */
        return $this;
    }
}
