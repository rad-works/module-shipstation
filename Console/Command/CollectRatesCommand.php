<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Console\Command;

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
            $rate = current($rates);
            foreach (Carrier::getDebugInfo() as $info) {
                $payload = json_decode($info['requestBody'], true);

                $package = $info['package'];
                $output->writeln('<info>ShipStation API Request Payload: </info>');
                $output->writeln('<info>' . print_r($payload, true) . '</info>');
            }
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
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
