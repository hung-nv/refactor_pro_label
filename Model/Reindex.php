<?php

namespace Swissup\ProLabels\Model;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A class to manage Magento modes
 *
 * @SuppressWarnings("PMD.CouplingBetweenObjects")
 * @SuppressWarnings("PMD.ExcessiveParameterList")
 */
class Reindex
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var \Swissup\ProLabels\Model\LabelFactory
     */
    private $labelFactory;

    /**
     * @param InputInterface                        $input
     * @param OutputInterface                       $output
     * @param \Swissup\ProLabels\Model\LabelFactory $labelFactory
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        \Swissup\ProLabels\Model\LabelFactory $labelFactory
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->labelFactory = $labelFactory;
    }

    /**
     * Reindex All Labels
     *
     * @return void
     */
    public function reindexAll()
    {
        $label = $this->labelFactory->create();
        $label->reindexAll();
    }
}
