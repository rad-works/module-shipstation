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

class Services implements OptionSourceInterface
{
    public function __construct(
        private readonly DataProviderInterface $dataProvider,
        private readonly Escaper               $escaper,
        private readonly ManagerInterface      $messageManager,
        private readonly LoggerInterface       $logger
    ) {
    }

    public function toOptionArray(mixed $services = null): array
    {
        $options = [];
        try {
            $domestic = [];
            $international = [];
            foreach ((is_array($services) ? $services: $this->dataProvider->getAllServices()) as $value => $service) {
                $carrier = $this->dataProvider->getAllCarriers()[$service->getCarrierCode()];
                $option = [
                    'value' => $this->escaper->escapeHtml($value),
                    'label' => $this->escaper->escapeHtml(sprintf(
                        '(%s) %s',
                        $carrier->getName(),
                        $service->getName()
                    ))
                ];
                if ($service->getInternational()) {
                    $international[] = $option;
                }

                if ($service->getDomestic()) {
                    $domestic[] = $option;
                }
            }

            if ($domestic) {
                $options[] = ['label' => __('Domestic'), 'value' => $domestic];
            }

            if ($international) {
                $options[] = ['label' => __('International'), 'value' => $international];
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Throwable $error) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while getting services from ShipStations API.')
            );
            $this->logger->critical($error);
        }

        return $options;
    }
}
