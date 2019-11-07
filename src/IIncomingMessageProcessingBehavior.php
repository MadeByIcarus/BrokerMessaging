<?php
declare(strict_types=1);

namespace Icarus\BrokerMessaging;


use Icarus\BrokerMessaging\Model\IncomingBrokerMessage;


interface IIncomingMessageProcessingBehavior
{

    public function apply(IncomingBrokerMessage $incomingBrokerMessage);
}