<?php

namespace Icarus\BrokerMessaging\Console\Commands;


use Icarus\BrokerMessaging\IncomingMessageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class ProcessCommand extends Command
{

    protected static $defaultName = 'broker-messaging:process';

    /**
     * @var IncomingMessageService
     */
    private $incomingBrokerMessageProcessor;



    public function __construct(IncomingMessageService $incomingBrokerMessageProcessor)
    {
        parent::__construct();

        $this->incomingBrokerMessageProcessor = $incomingBrokerMessageProcessor;
    }



    protected function configure()
    {
        $this->addOption("executionTime", "t", InputOption::VALUE_OPTIONAL, "Number of seconds. After this period it should be run again due to a possible memory depletion problem.", 60);
        $this->addOption("sleepTime", "s", InputOption::VALUE_OPTIONAL, "Delay in seconds between runs. Defaults to 15 seconds.", 15);
    }



    /**
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = time();
        $secondsToRun = $input->getOption("executionTime");
        $sleepTime = $input->getOption("sleepTime");

        do {
            $output->writeln("Processing batch...");
            $output->writeln("");

            foreach ($this->incomingBrokerMessageProcessor->processIncomingQueue() as $msg) {
                $output->writeln($msg);
            }

            $output->writeln("Sleeping for " . $sleepTime . "s...");
            sleep($sleepTime);
            $output->writeln("");
        } while ($secondsToRun > (time() - $start));
        return 0;
    }

}
