<?php

namespace Swissup\ProLabels\Block\Adminhtml\Label\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Rule\Model\Condition\AbstractCondition;

class Conditions extends Generic implements TabInterface
{
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_renderer_fieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->_renderer_fieldset = $rendererFieldset;
        $this->_conditions = $conditions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare content for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab class getter
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @return Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('prolabel');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param \Magento\CatalogRule\Api\Data\RuleInterface $model
     * @param string $fieldsetId
     * @param string $formName
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addTabToForm($model, $fieldsetId = 'conditions_fieldset', $formName = 'prolabels_label_form')
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('prolabels_');

        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);

        // init form
        $this->init_form($form, $fieldsetId, $formName, $conditionsFieldSetId, $model);

        $form->setValues($model->getData());
        $this->setConditionFormName($model->getConditions(), $formName, $conditionsFieldSetId);
        return $form;
    }

    /**
     * @param AbstractCondition $conditions
     * @param string $formName
     * @param string $jsFormName
     * @return void
     */
    private function setConditionFormName(AbstractCondition $conditions, $formName, $jsFormName)
    {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($jsFormName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }

    /**
     * {inherit}
     */
    private function init_form(&$form, $fieldsetId, $formName, $conditionsFieldSetId, $model) {
        $newChildUrl = $this->getUrl(
          'catalog_rule/promo_catalog/newConditionHtml/form/' . $conditionsFieldSetId,
          [
            'form_namespace' => $formName
          ]
        );

        $renderer = $this->_renderer_fieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
          ->setNewChildUrl($newChildUrl)
          ->setFieldSetId($conditionsFieldSetId);

        $fieldset = $form->addFieldset(
          $fieldsetId,
          [
            'legend' => __('Don\'t add conditions if label is applied to all products.')
          ]
        )->setRenderer($renderer);

        $fieldset->addField(
          'conditions',
          'text',
          [
            'name' => 'conditions',
            'label' => __('Conditions'),
            'title' => __('Conditions'),
            'required' => true,
            'data-form-part' => $formName
          ]
        )
          ->setRule($model)
          ->setRenderer($this->_conditions);
    }
}
