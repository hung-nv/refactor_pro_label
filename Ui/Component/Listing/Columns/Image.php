<?php
namespace Swissup\ProLabels\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\ObjectManagerInterface;

class Image extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;

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
            $fieldName = $this->getData('name');
            list($mode) = explode('_', $fieldName);
            foreach ($dataSource['data']['items'] as & $item) {
                if (!$item[$fieldName]) {
                    continue;
                }

                $mediaUrl = $this->storeManager->getStore()
                    ->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    );
                $imgUrl = $mediaUrl . "prolabels/{$mode}/" . $item[$fieldName ];
                $item[$fieldName . '_src'] = $imgUrl;
                $item[$fieldName . '_alt'] = $item[$fieldName];
                $item[$fieldName . '_orig_src'] = $imgUrl;
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'prolabels/label/edit',
                    [
                        'label_id' => $item['label_id']
                    ]
                );
            }
        }

        return $dataSource;
    }
}
