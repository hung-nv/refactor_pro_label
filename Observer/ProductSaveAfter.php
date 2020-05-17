<?php

namespace Swissup\ProLabels\Observer;

class ProductSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\ProLabels\Model\LabelFactory
     */
    protected $labelFactory;

    /**
     * @param \Swissup\ProLabels\Model\LabelFactory $labelFactory
     */
    public function __construct(
        \Swissup\ProLabels\Model\LabelFactory $labelFactory
    ) {
        $this->labelFactory = $labelFactory;
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

        $this->labelFactory->create()->reindex([$product->getId()]);
    }
}
