<?php
declare(strict_types=1);

namespace RadWorks\ShipStation\Block\Adminhtml\Form\Field\Renderer;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use RadWorks\ShipStation\Model\Api\DataProviderInterface;
use RadWorks\ShipStation\Model\Config\Source\Services;

class ActiveServices extends Select
{
    private DataProviderInterface $dataProvider;
    private Services $servicesSource;

    /**
     * Constructor.
     *
     * @param DataProviderInterface $dataProvider
     * @param Services $servicesSource
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        DataProviderInterface $dataProvider,
        Services              $servicesSource,
        Context               $context,
        array                 $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataProvider = $dataProvider;
        $this->servicesSource = $servicesSource;
    }

    /**
     * Render HTML
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions(
                $this->servicesSource->toOptionArray($this->dataProvider->getActiveServices())
            );
        }

        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     *
     * @return $this
     */
    public function setInputName($value): static
    {
        return $this->setName($value);
    }
}
