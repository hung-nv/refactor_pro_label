<?php

namespace Swissup\ProLabels\Model\ResourceModel;

/**
 * ProLabels Index mysql resource
 */
class Index extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_prolabels_label_index', 'index_id');
    }
}
