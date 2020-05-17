<?php

namespace Swissup\ProLabels\Model;

use Swissup\ProLabels\Api\Data\IndexInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Index  extends \Magento\Framework\Model\AbstractModel implements IndexInterface, IdentityInterface
{
    /**
     * cache tag
     */
    const CACHE_TAG = 'prolabels_Index';

    /**
     * @var string
     */
    protected $_cacheTag = 'prolabels_Index';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'prolabels_Index';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\Prolabels\Model\ResourceModel\Index');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get index_id
     *
     * return int
     */
    public function getIndexId()
    {
        return $this->getData(self::INDEX_ID);
    }

    /**
     * Get label_id
     *
     * return int
     */
    public function getLabelId()
    {
        return $this->getData(self::LABEL_ID);
    }

    /**
     * Get entity_id
     *
     * return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set index_id
     *
     * @param int $indexId
     * return \SWISSUP\Prolabels\Api\Data\IndexInterface
     */
    public function setIndexId($indexId)
    {
        return $this->setData(self::INDEX_ID, $indexId);
    }

    /**
     * Set label_id
     *
     * @param int $labelId
     * return \SWISSUP\Prolabels\Api\Data\IndexInterface
     */
    public function setLabelId($labelId)
    {
        return $this->setData(self::LABEL_ID, $labelId);
    }

    /**
     * Set entity_id
     *
     * @param int $entityId
     * return \SWISSUP\Prolabels\Api\Data\IndexInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }
}
