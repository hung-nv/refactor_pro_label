<?php

namespace Swissup\ProLabels\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

/**
 * ProLabels Label mysql resource
 */
class Label extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    /**
     * @var Configurable
     */
    protected $resourceProductConfigurable;

    /**
     * @param Configurable $resourceProductConfigurable
     * @param Context      $context
     * @param string       $connectionName
     */
    public function __construct(
        Configurable $resourceProductConfigurable,
        Context $context,
        $connectionName = null
    ) {
        $this->resourceProductConfigurable = $resourceProductConfigurable;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_prolabels_label', 'label_id');
    }

    protected function _afterLoad(AbstractModel $object)
    {
        return $this;
    }

    protected function _afterSave(AbstractModel $object)
    {
        return $this;
    }

    protected function _afterDelete(AbstractModel $rule)
    {
        return $this;
    }

    public function deleteIndexes($productIds = [])
    {
        try {
            $whereClause = empty($productIds)
                ? null // delete all when no product ids provided
                : ['entity_id IN (?)' => $productIds];
            $this->getConnection()->delete(
                $this->getTable('swissup_prolabels_index'),
                $whereClause
            );
        } catch (\Exception $e) {
            return $this;
        }

        return $this;
    }

    public function addLabelIndexes($data)
    {
        try {
            $connection = $this->getConnection();
            $connection->insertMultiple(
                $this->getTable('swissup_prolabels_index'), $data);
        } catch (\Exception $e) {
            return $this;
        }

        return $this;
    }

    public function getIndexedProducts($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('swissup_prolabels_index'),
            'entity_id'
        )->where(
            'label_id = :label_id'
        );
        $binds = [':label_id' => (int)$id];
        return $connection->fetchCol($select, $binds);
    }

    public function getItemsToReindex($count, $step)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('catalog_product_entity'),
            'entity_id'
        )->order('entity_id')
        ->limit($count, $count * $step);

        return $connection->fetchCol($select);
    }

    /**
     * Get super links for product IDs
     *
     * @param  array  $ids
     * @return array
     */
    public function validateProductSuperLink(array $ids)
    {
        $super = $this->resourceProductConfigurable->getParentIdsByChild($ids);
        return $super ?: [];
    }

    public function getProductLabels($productId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('swissup_prolabels_index'),
            'label_id'
        )->where('entity_id = ?', $productId);

        return $connection->fetchCol($select);
    }

    public function getCatalogLabels($productIds)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('swissup_prolabels_index'),
            ['label_id', 'entity_id']
        )->where('entity_id IN (?)', $productIds);
        $indexData = $connection->fetchAll($select);
        $allLabelIds = [];
        foreach ($indexData as $item) {
            $allLabelIds[] = $item['label_id'];
        }

        $allLabelIds = array_unique($allLabelIds);

        $select = $connection->select()->from(
            $this->getTable('swissup_prolabels_label')
        )->where('label_id IN (?)', $allLabelIds)
         ->where('status = ?', 1);

        $labelSelectData = $connection->fetchAll($select);

        if (count($labelSelectData) === 0) {
            return [];
        }

        $labelData = [];
        foreach ($labelSelectData as $label) {
            $labelData[$label['label_id']] = $label;
        }
        $result = [];
        foreach ($indexData as $index) {
            if (isset($labelData[$index['label_id']])) {
                $result[$index['entity_id']][$index['label_id']] = $labelData[$index['label_id']];
            }
        }

        return $result;
    }

    /**
     * Get IDs of children for super product
     *
     * @param  int|array $superId
     * @return array
     */
    public function getChildrenIdsForSuperProduct($superId)
    {
        $groups = $this->resourceProductConfigurable->getChildrenIds($superId);
        $ids = [];
        foreach ($groups as $children) {
            $ids = array_merge($ids, $children);
        }

        return array_unique($ids);
    }
}
