<?php

namespace Swissup\ProLabels\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;

class Catalog extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Swissup\ProLabels\Model\LabelsProvider
     */
    protected $labelsProvider;

    /**
     * @var \Swissup\ProLabels\Model\LabelsModifier
     */
    protected $labelsModifier;

    /**
     * @var \Swissup\ProLabels\Model\Renderer\Amp
     */
    protected $rendererAmp;

    /**
     * @var \Swissup\ProLabels\Model\Config\Source\Position
     */
    protected $positionSource;

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
        $this->labelsProvider = $labelsProvider;
        $this->labelsModifier = $labelsModifier;
        $this->rendererAmp = $rendererAmp;
        $this->positionSource = $positionSource;
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
     * @param  int $productId
     * @return string
     */
    public function toHtmlProductLabels($productId, $mode = 'category')
    {
        $labels = $this->labelsProvider->getLabels($productId, $mode);
        if (!$labels || !$labels->getLabelsData()) {
            return '';
        }

        // When Swissup AMP enabled render labels on server side.
        // Only image labels.
        if ($this->isSwissupAmpEnabled()) {
            return $this->renderImageLabels($labels, $mode);
        }

        // Render labels with JS. Init jquery widget.
        $mageInit = [
            'Swissup_ProLabels/js/prolabels' => $this->getJsWidgetOptions($labels)
        ];

        return "<div data-mage-init='{$this->jsonEncode($mageInit)}'></div>";
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
     * @param  string           $mode
     * @return \Magento\Framework\DataObject
     */
    public function getLabels(ProductInterface $product, $mode = 'category')
    {
        return $this->labelsProvider->initialize($product, $mode);
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
    public function isSwissupAmpEnabled()
    {
        if ($this->isModuleOutputEnabled('Swissup_Amp')) {
            $helperAmp = ObjectManager::getInstance()->get(
                '\Swissup\Amp\Helper\Data'
            );
            if ($helperAmp->canUseAmp()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  \Magento\Framework\DataObject $labels
     * @return string
     */
    public function renderImageLabels(\Magento\Framework\DataObject $labels)
    {
        $this->labelsModifier->modify($labels);
        $skipPositions = ['content'];
        return $this->rendererAmp->render($labels, $skipPositions);
    }

    /**
     * @param  \Magento\Framework\DataObject $labels
     * @return string
     */
    public function renderContentLabels(\Magento\Framework\DataObject $labels)
    {
        $this->labelsModifier->modify($labels);
        $skipPositions = [];
        foreach ($this->positionSource->toOptionArray() as $item) {
            if ($item['value'] === 'content') {
                continue;
            }

            $skipPositions[] = $item['value'];
        }

        return $this->rendererAmp->render($labels, $skipPositions);
    }
}
