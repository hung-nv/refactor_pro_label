<?php

namespace Swissup\ProLabels\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\State;
use Magento\Backend\App\Area\FrontNameResolver;

/**
 * Command for reindexing labels
 */
class ReindexAllCommand extends Command
{
    /**
     * @var AppState
     */
    protected $appState;

    /**
     * Object manager factory
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Magento\Framework\App\State $appState
        )
    {
        $this->objectManager = $objectManager;
        $this->appState = $appState;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $description = 'Reindex All Product Labels';

        $this->setName('prolabels:reindex:all')
            ->setDescription($description);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(FrontNameResolver::AREA_CODE);
        try {
            /** @var \Swissup\ProLabels\Model\Reindex $reindex */
            $reindex = $this->objectManager->create(
                'Swissup\ProLabels\Model\Reindex',
                [
                    'input' => $input,
                    'output' => $output,
                ]
            );
            $reindex->reindexAll();
            $output->writeln('Labels have been reindexed.');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return;
        }
    }
}
