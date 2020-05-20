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
    protected $app_state;

    /**
     * Object manager factory
     *
     * @var ObjectManagerInterface
     */
    private $object_manager;

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
        $this->object_manager = $objectManager;
        $this->app_state = $appState;
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
        $this->app_state->setAreaCode(FrontNameResolver::AREA_CODE);
        try {
            /** @var \Swissup\ProLabels\Model\Reindex $reindex */
            $reindex = $this->object_manager->create(
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
