<?php

namespace Swissup\ProLabels\Model\ResourceModel\Label;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\ProLabels\Model\Label', 'Swissup\ProLabels\Model\ResourceModel\Label');
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $label = $this->_entityFactory->create('Swissup\ProLabels\Model\Label');
        foreach ($this as $item) {
            $label->setStoreId($item->getStoreId());
            $item->setStoreId($label->getStoreId());
        }

        return parent::_afterLoad();
    }
}
