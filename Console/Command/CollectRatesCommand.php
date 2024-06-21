<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Console\Command;

use DmiRud\ShipStation\Model\Api\Data\RateInterface;
use DmiRud\ShipStation\Model\Api\RequestInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Console\Cli;
use DmiRud\ShipStation\Console\Command\CollectRates\RatesProviderFactory;
use DmiRud\ShipStation\Model\Carrier;

class CollectRatesCommand extends Command
{
    public const NAME = 'shipstation:rates:collect';
    private const ARG_DEST_POSTCODE = 'postcode';
    private const ARG_SKUS = 'skus';
    private const OPTION_DEST_COUNTRY = 'country';
    private const OPTION_STORE_ID = 'store_id';
    private readonly RatesProviderFactory $ratesProviderFactory;

    public function __construct(RatesProviderFactory $ratesProviderFactory, string $name = self::NAME)
    {
        $this->ratesProviderFactory = $ratesProviderFactory;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription(
                'Collect ShipStation rates by products\' skus and shipment destination postcode.'
            )->setDefinition([
                new InputArgument(
                    self::ARG_DEST_POSTCODE,
                    InputArgument::REQUIRED,
                    'Shipment destination postcode'
                ),
                new InputArgument(
                    self::ARG_SKUS,
                    InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                    'List of the skus in the shipment'
                ),
                new InputOption(
                    self::OPTION_DEST_COUNTRY,
                    null,
                    InputArgument::OPTIONAL,
                    'Shipment destination country code(US by default)',
                    AbstractCarrier::USA_COUNTRY_ID
                ),
                new InputOption(
                    self::OPTION_STORE_ID,
                    null,
                    InputArgument::OPTIONAL,
                    'Store id (1 by default)',
                    '1'
                )
            ]);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $rates = $this->ratesProviderFactory->create()->get(
                $input->getArgument(self::ARG_DEST_POSTCODE),
                $input->getArgument(self::ARG_SKUS),
                $input->getOption(self::OPTION_DEST_COUNTRY),
                $input->getOption(self::OPTION_STORE_ID)
            );
            $this->writeRequestInfo($output);
            $this->writeRatesInfo(current($rates), $output, $rates);
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param mixed $rate
     * @param OutputInterface $output
     * @param $rates
     * @return void
     */
    private function writeRatesInfo(mixed $rate, OutputInterface $output, $rates): void
    {
        if ($rate) {
            $table = new Table($output);
            $table->setHeaders(array_keys(current($rates)->toArray()));
            $table->addRows(array_map(fn($rate) => $rate->toArray(), $rates));
            $output->writeln('<info>ShipStation API Collected Rates:</info>');
            $table->render();
            $output->writeln('<info>Done!</info>');
        } else {
            $output->writeln('<error>No Rates Collected.</error>');
        }
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    private function writeRequestInfo(OutputInterface $output): void
    {
        $number = 1;
        foreach (Carrier::getDebugInfo() as $info) {
            /** @var RequestInterface $request */
            foreach ($info['requests'] as $request) {
                $payload = $request->getPayload();
                $rate = $request->getPackage()->getRate();
                $failed = in_array($request->getService()->getCode(), Carrier::getRequests(Carrier::RATE_REQUEST_STATUS_FAILED, true));
                $service = $request->getService()->getName() . ' (' . $request->getService()->getCode() . ')';
                $from = sprintf('%s, %s, %s', $payload['fromPostalCode'], $payload['fromCity'], $payload['fromState']);
                $to = sprintf('%s, %s', $payload['toPostalCode'], $payload['toCountry']);
                $output->writeln('<info>ShipStation API Request/Package  #' . $number++ . ':</info>');
                $output->writeln($failed ? '<error>Status: Failed</error>' : '<info>Status: Success</info>');
                $output->writeln('<info>Service: ' . $service . '</info>');
                $output->writeln('<info>Skus: ' . implode(',', $request->getPackage()->getProductsSkus()) . '</info>');
                $output->writeln('<info>From: ' . $from . '</info>');
                $output->writeln('<info>To: ' . $to . '</info>');
                $units = $payload['dimensions']['units'];
                $output->writeln(
                    '<info>Weight: ' . $payload['weight']['value'] . ' ' . $payload['weight']['units'] . '</info>'
                );
                foreach ($payload['dimensions'] as $dimension => $value) {
                    if ($dimension == 'units') {
                        continue;
                    }
                    $output->writeln('<info>' . ucfirst($dimension) . ': ' . $value . ' ' . $units . '</info>');
                }
                if ($rate) {
                    $output->writeln('<info>Cost: ' . $rate->getShipmentCost() . '</info>');
                    $output->writeln('<info>Other Cost: ' . $rate->getOtherCost() . '</info>');
                    $output->writeln('<info>Adjusment Modifier: ' . $rate->getCostAdjustmentModifier() . '</info>');
                }
                $output->writeln(PHP_EOL);
            }
        }
    }
}
