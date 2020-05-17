<?php

namespace Swissup\ProLabels\Ui\DataProvider\Form;

use Swissup\ProLabels\Model\ResourceModel\Label\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\CatalogRule\Model\Rule\CustomerGroupsOptionsProvider;

class LabelDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * ClientRequest collection
     *
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var array
     */
    protected $fileInfo;

    /**
     * @var CustomerGroupsOptionsProvider
     */
    protected $customerGroups;

    /**
     * @param string                        $name
     * @param string                        $primaryFieldName
     * @param string                        $requestFieldName
     * @param CollectionFactory             $collectionFactory
     * @param DataPersistorInterface        $dataPersistor
     * @param array                         $fileInfo
     * @param CustomerGroupsOptionsProvider $customerGroups
     * @param array                         $meta
     * @param array                         $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        $fileInfo = [],
        CustomerGroupsOptionsProvider $customerGroups,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->fileInfo = $fileInfo;
        $this->customerGroups = $customerGroups;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        /** @var \Magento\Cms\Model\Block $block */
        foreach ($items as $label) {
            $label->setData('customer_groups', $label->getCustomerGroups());
            $label->setData('store_id', $label->getStoreId());
            // prepare image data for ui element
            foreach (['product', 'category'] as $mode) {
                $imageName = $label->getImageName($mode);
                if ($imageName && is_string($imageName)) {
                    $label->setData(
                        $mode . '_image',
                        [ $this->fileInfo[$mode]->getImageData($imageName) ]
                    );
                }
            }

            $this->loadedData[$label->getId()] = $label->getData();
        }

        $data = $this->dataPersistor->get('prolabels_label');
        if (!empty($data)) {
            $label = $this->collection->getNewEmptyItem();
            // update image data, if image was just uploaded
            foreach (['product', 'category'] as $mode) {
                if (isset($data["{$mode}_image"])
                    && is_array($data["{$mode}_image"])
                    && isset($data["{$mode}_image"][0]['name'])
                    && isset($data["{$mode}_image"][0]['tmp_name'])
                ) {
                    $label->setData("{$mode}_image", $data["{$mode}_image"][0]['name']);
                    $data["{$mode}_image"] = [
                        $this->fileInfo[$mode]->getImageData($imageName)
                    ];
                }
            }

            $label->setData($data);
            $this->loadedData[$label->getLabelId()] = $label->getData();
            $this->dataPersistor->clear('prolabels_label');
        }

        if (!$this->loadedData) {
            // new label default values
            $this->loadedData[null] = [
                'customer_groups' => array_map(
                    'strval',
                    array_keys(
                        $this->customerGroups->toOptionArray()
                    )
                )
            ];
        }

        return $this->loadedData;
    }
}
