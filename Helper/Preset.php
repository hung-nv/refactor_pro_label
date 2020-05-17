<?php

namespace Swissup\ProLabels\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

class Preset extends AbstractHelper
{
    /**
     * @var \Swissup\ProLabels\Block\Preset\Template
     */
    protected $renderer;

    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(
        \Swissup\ProLabels\Block\Preset\Template $renderer,
        Context $context
    ) {
        $this->renderer = $renderer;
        parent::__construct($context);
    }

    /**
     * Get value from encoded array base on label type from request
     *
     * @param  string $encodedValues
     * @return mixed
     */
    public function getValue($encodedValues)
    {
        $decodeAsArray = true;
        $values = json_decode($encodedValues, $decodeAsArray);
        $labelType = $this->_getRequest()->getParam('type');
        if (is_array($values)) {
            $key = array_key_exists($labelType, $values)
                ? $labelType
                : 'default';
            return isset($values[$key]) ? $values[$key] : '';
        }

        return $encodedValues;
    }

    /**
     * Render preset option template
     *
     * @param  string $template
     * @param  string $encodedData
     * @return string
     */
    public function renderTemplate(
        $template,
        $encodedData = '{}'
    ) {
        $decodeAsArray = true;
        $this->renderer->setTemplate($template);
        $this->renderer->setData(json_decode($encodedData, $decodeAsArray));
        return $this->renderer->render();
    }

    /**
     * Get label text in template
     *
     * @return string
     */
    public function getLabelText()
    {
        $text = $this->getValue(json_encode($this->renderer->getLabelText()));
        return $text ?: 'Label';
    }
}
