<?php
declare(strict_types=1);

namespace Icarus\BrokerMessaging\DI;


use Icarus\BrokerMessaging\IncomingBrokerMessageReceiver;
use Icarus\BrokerMessaging\OutgoingBrokerMessageQueue;
use Nette\DI\CompilerExtension;
use Nettrine\ORM\DI\Traits\TEntityMapping;


class BrokerMessageSenderExtension extends CompilerExtension
{

    use TEntityMapping;



    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        //

        $builder->addDefinition($this->prefix('brokerMessageQueue'))
            ->setFactory(OutgoingBrokerMessageQueue::class);

        $builder->addDefinition($this->prefix('brokerMessageReceiver'))
            ->setFactory(IncomingBrokerMessageReceiver::class);

        $this->setEntityMappings($this->getEntityMappings());
    }



    private function getEntityMappings(): array
    {
        return [
            'Icarus\BrokerMessaging' => __DIR__ . '/../Model/'
        ];
    }
}