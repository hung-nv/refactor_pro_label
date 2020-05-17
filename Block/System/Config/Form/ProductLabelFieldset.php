<?php

namespace Swissup\ProLabels\Block\System\Config\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;

class ProductLabelFieldset extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @param \Magento\Catalog\Helper\Image       $imageHelper
     * @param \Magento\Backend\Block\Context      $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js   $jsHelper
     * @param array                               $data
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        array $data = []
    ) {
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }


    /**
     * {@inheritdoc}
     */
    public function render(AbstractElement $element)
    {
        $html = parent::render($element);
        $html = str_replace(
            '<fieldset class="config',
            '<fieldset data-mage-init=\''
                . json_encode($this->getMageInitArray())
                . '\' class="config',
            $html
        );
        return $html;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getHeaderCommentHtml($element)
    {
        return parent::_getHeaderCommentHtml($element)
            . $this->_getPreviewPlaceholderHtml();
    }

    /**
     * {@inheritdoc}
     */
    protected function _getPreviewPlaceholderHtml()
    {
        $html = '<div class="preview">';
        $html .= '<div class="image-placeholder prolabels-wrapper"></div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Build array for data-mage-init
     *
     * @return array
     */
    protected function getMageInitArray()
    {
        return [
            'Swissup_ProLabels/js/preview' => [
                'demoData' => [
                    'productImage' => $this->imageHelper->getDefaultPlaceholderUrl("image"),
                    'productName' => 'Demo Product',
                    'price' => 32,
                    'specialPrice' => 24.99,
                    'sku' => 'DEMO-PROD'
                ]
            ]
        ];
    }
}
