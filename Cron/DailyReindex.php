<?php
namespace Swissup\ProLabels\Cron;

use Swissup\ProLabels\Model\Label;

class DailyReindex
{
    /**
     * @var \Swissup\ProLabels\Model\Label
     */
    protected $processor;
    /**
     * Scope Config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Swissup\ProLabels\Model\Label $processor
     */
    public function __construct(
        \Swissup\ProLabels\Model\Label $processor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->processor = $processor;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Regenerate label indexes
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_scopeConfig->getValue("prolabels/general/cron")) {
            $this->processor->reindexAll();
        }
    }
}
