<?php

namespace Icarus\BrokerMessaging\Console\Commands;


use Icarus\BrokerMessaging\OutgoingBrokerMessageQueue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class PublishCommand extends Command
{

    protected static $defaultName = 'broker-messaging:publish';

    /**
     * @var OutgoingBrokerMessageQueue
     */
    private $outgoingBrokerMessageQueue;



    public function __construct(OutgoingBrokerMessageQueue $outgoingBrokerMessageQueue)
    {
        parent::__construct();

        $this->outgoingBrokerMessageQueue = $outgoingBrokerMessageQueue;
    }



    /**
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outgoingBrokerMessageQueue->publishQueuedMessages();
        return 0;
    }

}
