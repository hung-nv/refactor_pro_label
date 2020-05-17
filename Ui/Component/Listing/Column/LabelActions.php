<?php

namespace Swissup\ProLabels\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class LabelActions extends Column
{
    /** Url path */
    const URL_PATH_EDIT = 'prolabels/label/edit';
    const URL_PATH_DELETE = 'prolabels/label/delete';
    const URL_PATH_ENABLE = 'prolabels/label/enable';
    const URL_PATH_DISABLE = 'prolabels/label/disable';

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['label_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'label_id' => $item['label_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'label_id' => $item['label_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "${ $.$data.title }"'),
                                'message' => __('Are you sure you wan\'t to delete a "${ $.$data.title }" record?')
                            ]
                        ],
                        'enable' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_ENABLE,
                                [
                                    'label_id' => $item['label_id']
                                ]
                            ),
                            'label' => __('Enable')
                        ],
                        'disable' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DISABLE,
                                [
                                    'label_id' => $item['label_id']
                                ]
                            ),
                            'label' => __('Disable')
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
