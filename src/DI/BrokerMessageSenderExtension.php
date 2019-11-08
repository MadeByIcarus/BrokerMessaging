<?php
declare(strict_types=1);

namespace Icarus\BrokerMessaging\DI;


use Icarus\BrokerMessaging\Console\Commands\ProcessCommand;
use Icarus\BrokerMessaging\Console\Commands\PublishCommand;
use Icarus\BrokerMessaging\IncomingMessageService;
use Icarus\BrokerMessaging\OutgoingMessageService;
use Nette\DI\CompilerExtension;
use Nettrine\ORM\DI\Traits\TEntityMapping;


class BrokerMessageSenderExtension extends CompilerExtension
{

    use TEntityMapping;



    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        //

        $builder->addDefinition($this->prefix('brokerMessageSender'))
            ->setFactory(OutgoingMessageService::class);

        $builder->addDefinition($this->prefix('brokerMessageReceiver'))
            ->setFactory(IncomingMessageService::class);

        $builder->addDefinition($this->prefix('publishCommand'))
            ->setFactory(PublishCommand::class);

        $builder->addDefinition($this->prefix('processCommand'))
            ->setFactory(ProcessCommand::class);

        $this->setEntityMappings($this->getEntityMappings());
    }



    private function getEntityMappings(): array
    {
        return [
            'Icarus\BrokerMessaging' => __DIR__ . '/../Model/'
        ];
    }
}