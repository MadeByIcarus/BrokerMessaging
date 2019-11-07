<?php
declare(strict_types=1);

namespace Icarus\BrokerMessaging;


use Icarus\BrokerMessaging\Model\IncomingBrokerMessage;


interface IIncomingMessageProcessor
{

    public function process(IncomingBrokerMessage $incomingBrokerMessage);
}