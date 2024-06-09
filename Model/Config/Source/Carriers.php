<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use DmiRud\ShipStation\Model\Api\DataProviderInterface;

class Carriers implements OptionSourceInterface
{
    public function __construct(
        private readonly DataProviderInterface $dataProvider,
        private readonly Escaper               $escaper,
        private readonly ManagerInterface      $messageManager,
        private readonly LoggerInterface       $logger
    ) {
    }

    public function toOptionArray(): array
    {
        $options = [];
        try {
            foreach ($this->dataProvider->getAllCarriers() as $carrier) {
                $options[] = [
                    'value' => $this->escaper->escapeHtml($carrier->getCode()),
                    'label' => $this->escaper->escapeHtml($carrier->getName())
                ];
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Throwable $error) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while getting carriers from ShipStations API.')
            );
            $this->logger->critical($error);
        }

        return $options;
    }
}
