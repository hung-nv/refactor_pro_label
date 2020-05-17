<?php

namespace Swissup\ProLabels\Api\Data;

interface IndexInterface
{
    CONST INDEX_ID = 'index_id';
    CONST LABEL_ID = 'label_id';
    CONST ENTITY_ID = 'entity_id';

    /**
     * Get index_id
     *
     * return int
     */
    public function getIndexId();

    /**
     * Get label_id
     *
     * return int
     */
    public function getLabelId();

    /**
     * Get entity_id
     *
     * return int
     */
    public function getEntityId();


    /**
     * Set index_id
     *
     * @param int $indexId
     * return \SWISSUP\Prolabels\Api\Data\IndexInterface
     */
    public function setIndexId($indexId);

    /**
     * Set label_id
     *
     * @param int $labelId
     * return \SWISSUP\Prolabels\Api\Data\IndexInterface
     */
    public function setLabelId($labelId);

    /**
     * Set entity_id
     *
     * @param int $entityId
     * return \SWISSUP\Prolabels\Api\Data\IndexInterface
     */
    public function setEntityId($entityId);

}
