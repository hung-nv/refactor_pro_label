<?php
namespace Swissup\ProLabels\Controller\Adminhtml\Label;

class Disable extends Enable
{
    /**
     * @return int
     */
    public function getStatusCode()
    {
        return 0;
    }

    /**
     * Add success message about status change
     */
    protected function addSuccessMessage(
        \Swissup\ProLabels\Model\Label $label
    ) {
        $this->messageManager->addSuccess(
            __('Label "%1" was disabled.', $label->getTitle())
        );
    }
}
