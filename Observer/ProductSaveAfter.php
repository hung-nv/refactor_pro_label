<?php

namespace Swissup\ProLabels\Observer;

class ProductSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\ProLabels\Model\LabelFactory
     */
    protected $label_factory;

    /**
     * @param \Swissup\ProLabels\Model\LabelFactory $labelFactory
     */
    public function __construct(
        \Swissup\ProLabels\Model\LabelFactory $labelFactory
    ) {
        $this->label_factory = $labelFactory;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();

        if (!$product) {
            return;
        }

        $this->label_factory->create()->reindex([$product->getId()]);
    }
}
