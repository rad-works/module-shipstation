<?php
declare(strict_types=1);

namespace DmiRud\ShipStation\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use DmiRud\ShipStation\Block\Adminhtml\Form\Field\Renderer\ActiveServices;
use DmiRud\ShipStation\Model\Api\DataProviderInterface;
use DmiRud\ShipStation\Model\Carrier\ServiceRestrictionsInterface;
use DmiRud\ShipStation\Model\Config\Source\Services;

class ServiceRestrictions extends AbstractFieldArray
{
    /**
     * @var AbstractBlock[]
     */
    protected array $renderers = [];

    private DataProviderInterface $dataProvider;
    private Services $servicesSource;

    public function __construct(
        DataProviderInterface $dataProvider,
        Services              $servicesSource,
        Context               $context,
        array                 $data = [],
        ?SecureHtmlRenderer   $secureRenderer = null
    ) {
        parent::__construct($context, $data, $secureRenderer);
        $this->dataProvider = $dataProvider;
        $this->servicesSource = $servicesSource;
    }

    /**
     * Prepare to render
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn(ServiceRestrictionsInterface::FIELD_SERVICE, [
            'label' => __('Service'),
            'renderer' => $this->getRenderer(ActiveServices::class)->setExtraParams('style="width:300px"')
        ]);
        $this->addColumn(ServiceRestrictionsInterface::FIELD_MAX_LENGTH, [
            'label' => __('Max Length (in)'),
            'style' => 'width:50px',
            'class' => 'required-entry validate-zero-or-greater admin__control-text'
        ]);
        $this->addColumn(ServiceRestrictionsInterface::FIELD_MAX_LENGTH_WITH_GIRTH, [
            'label' => __('Max Length + Girth (in)'),
            'style' => 'width:50px',
            'class' => 'required-entry validate-zero-or-greater admin__control-text'
        ]);
        $this->addColumn(ServiceRestrictionsInterface::FIELD_MAX_WEIGHT, [
            'label' => __('Max Weight (lbs)'),
            'style' => 'width:50px',
            'class' => 'required-entry validate-zero-or-greater admin__control-text'
        ]);
        $this->addColumn(ServiceRestrictionsInterface::FIELD_DIMENSIONAL_WEIGHT, [
            'label' => __('Dimensional Wt Divisor'),
            'style' => 'width:50px',
            'class' => 'required-entry validate-zero-or-greater admin__control-text'
        ]);
        $this->addColumn(ServiceRestrictionsInterface::FIELD_RATE_ADJUSTMENT, [
            'label' => __('Shipping Rate Adjustment'),
            'style' => 'width:50px',
            'class' => 'required-entry validate-number admin__control-text'
        ]);
        $this->addColumn(ServiceRestrictionsInterface::FIELD_SUBTOTAL_ADJUSTMENT, [
            'label' => __('Order Subtotal Customs Surcharge'),
            'style' => 'width:50px',
            'class' => 'validate-number admin__control-text'
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Restriction');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        if ($service = $row->getData(ServiceRestrictionsInterface::FIELD_SERVICE)) {
            $options['option_' . $this->getRenderer(ActiveServices::class)->calcOptionHash($service)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Provides renderer block
     *
     * @param string $class
     * @return AbstractBlock
     * @throws LocalizedException
     */
    protected function getRenderer(string $class): AbstractBlock
    {
        if (!array_key_exists($class, $this->renderers)) {
            $this->renderers[$class] = $this->getLayout()->createBlock(
                $class,
                '',
                ['data' => ['value' => $this->getValue(), 'is_render_to_js_template' => true]]
            );
        }

        return $this->renderers[$class];
    }
}
