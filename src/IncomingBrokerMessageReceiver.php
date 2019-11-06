<?php
declare(strict_types=1);

namespace Icarus\BrokerMessaging;


use Icarus\BrokerMessaging\Model\IncomingBrokerMessage;
use Nettrine\ORM\EntityManagerDecorator;
use PhpAmqpLib\Message\AMQPMessage;


class IncomingBrokerMessageReceiver
{

    /**
     * @var EntityManagerDecorator
     */
    private $entityManager;

    public $messageSavedCallback;



    public function __construct(EntityManagerDecorator $entityManager)
    {
        $this->entityManager = $entityManager;
    }



    public function saveIncomingMessage(AMQPMessage $AMQPMessage, bool $doFlush = true)
    {
        $message = new IncomingBrokerMessage($AMQPMessage->getBody());
        $this->entityManager->persist($message);

        $doFlush && $this->entityManager->flush();

        if (is_callable($this->messageSavedCallback)) {
            call_user_func($this->messageSavedCallback, $message);
        }
    }
}