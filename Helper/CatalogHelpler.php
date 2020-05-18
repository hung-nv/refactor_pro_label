<?php

namespace Swissup\ProLabels\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;

class CatalogHelpler extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Swissup\ProLabels\Model\LabelsProvider
     */
    protected $labels_provider;

    /**
     * @var \Swissup\ProLabels\Model\LabelsModifier
     */
    protected $labels_modifier;

    /**
     * @var \Swissup\ProLabels\Model\Renderer\Amp
     */
    protected $renderer_amp;

    /**
     * @var \Swissup\ProLabels\Model\Config\Source\Position
     */
    protected $position_source;

    /**
     * @param \Swissup\ProLabels\Model\LabelsProvider         $labelsProvider
     * @param \Swissup\ProLabels\Model\LabelsModifier         $labelsModifier
     * @param \Swissup\ProLabels\Model\Renderer\Amp           $rendererAmp
     * @param \Swissup\ProLabels\Model\Config\Source\Position $positionSource
     * @param \Magento\Framework\App\Helper\Context           $context
     */
    public function __construct(
        \Swissup\ProLabels\Model\LabelsProvider $labelsProvider,
        \Swissup\ProLabels\Model\LabelsModifier $labelsModifier,
        \Swissup\ProLabels\Model\Renderer\Amp $rendererAmp,
        \Swissup\ProLabels\Model\Config\Source\Position $positionSource,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->labels_provider = $labelsProvider;
        $this->labels_modifier = $labelsModifier;
        $this->renderer_amp = $rendererAmp;
        $this->position_source = $positionSource;
        parent::__construct($context);
    }

    /**
     * Left for compatibility with older versions
     *
     * @return Get On Sale Label Data
     * @deprecated since 1.1.0
     */
    public function getProductLabels($product)
    {
        return '';
    }

    /**
     * @param  int $product_id
     *
     * @return string
     */
    public function toHtmlProductLabels($product_id, $instance = 'category')
    {
        $labels = $this->labels_provider->getLabels($product_id, $instance);
        if (!$labels || !$labels->getLabelsData()) {
            return '';
        }

        // When Swissup AMP enabled render labels on server side.
        // Only image labels.
        if ($this->is_swissup_amp_enabled()) {
            return $this->renderImageLabels($labels, $instance);
        }

        // Render labels with JS. Init jquery widget.
        $mage_init = [
            'Swissup_ProLabels/js/prolabels' => $this->getJsWidgetOptions($labels)
        ];

        return "<div data-mage-init='{$this->jsonEncode($mage_init)}'></div>";
    }

    /**
     * @return string
     */
    public function getListItemSelector()
    {
        return $this->scopeConfig->getValue(
            'prolabels/output/category_item',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getImageSelector()
    {
        return $this->scopeConfig->getValue(
            'prolabels/output/category_base',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getContentSelector()
    {
        return $this->scopeConfig->getValue(
            'prolabels/output/category_content',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get init options for JS widget of category labels
     *
     * @param  \Magento\Framework\DataObject $labels
     * @param  array                         $defaults
     * @return array
     */
    public function getJsWidgetOptions(
        \Magento\Framework\DataObject $labels,
        $defaults = ['contentLabelsInsertion' => 'insertAfter']
    ) {
        $options = [
            'parent' => $this->getListItemSelector(),
            'imageLabelsTarget' => $this->getImageSelector(),
            'contentLabelsTarget' => $this->getContentSelector(),
            'labelsData' => $labels->getLabelsData(),
            'predefinedVars' => $labels->getPredefinedVariables()
        ];

        return $options + $defaults;
    }

    /**
     * Get labels for product
     *
     * @param  ProductInterface $product
     * @param  string           $instance
     *
     * @return \Magento\Framework\DataObject
     */
    public function get_labels(ProductInterface $product, $instance = 'category')
    {
        return $this->labels_provider->initialize($product, $instance);
    }

    /**
     * To JSON string
     *
     * @param  array $array
     * @return string
     */
    public function jsonEncode($array)
    {
        return json_encode($array, JSON_HEX_APOS);
    }

    /**
     * @return boolean
     */
    public function is_swissup_amp_enabled()
    {
        $return = false;

        if ($this->isModuleOutputEnabled('Swissup_Amp')) {
            $helperAmp = ObjectManager::getInstance()->get(
                '\Swissup\Amp\Helper\Data'
            );
            if ($helperAmp->canUseAmp()) {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * @param  \Magento\Framework\DataObject $labels
     * @return string
     */
    public function renderImageLabels(\Magento\Framework\DataObject $labels)
    {
        $this->labels_modifier->modify($labels);
        $skip_positions = ['content'];
        return $this->renderer_amp->render($labels, $skip_positions);
    }

    /**
     * @param  \Magento\Framework\DataObject $labels
     * @return string
     */
    public function renderContentLabels(\Magento\Framework\DataObject $labels)
    {
        $this->labels_modifier->modify($labels);
        $skip_positions = [];
        foreach ($this->position_source->toOptionArray() as $item) {
            if ($item['value'] === 'content') {
                continue;
            }

            $skip_positions[] = $item['value'];
        }

        return $this->renderer_amp->render($labels, $skip_positions);
    }
}
