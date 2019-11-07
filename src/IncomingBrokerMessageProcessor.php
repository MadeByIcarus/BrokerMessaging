<?php
declare(strict_types=1);

namespace Icarus\BrokerMessaging;


use Icarus\BrokerMessaging\Model\IncomingBrokerMessage;
use Icarus\RabbitMQ\IAMQPMessageProcessor;
use Icarus\RabbitMQ\IConsumer;
use Nettrine\ORM\EntityManagerDecorator;
use PhpAmqpLib\Message\AMQPMessage;
use Tracy\Debugger;


class IncomingBrokerMessageProcessor implements IAMQPMessageProcessor
{

    /**
     * @var EntityManagerDecorator
     */
    private $entityManager;

    /**
     * @var IIncomingMessageProcessingBehavior
     */
    private $incomingMessageProcessingBehavior;



    public function __construct(
        EntityManagerDecorator $entityManager,
        IIncomingMessageProcessingBehavior $incomingMessageProcessingBehavior = null
    )
    {
        $this->entityManager = $entityManager;
        $this->incomingMessageProcessingBehavior = $incomingMessageProcessingBehavior;
    }



    public function process(AMQPMessage $message): int
    {
        try {
            $brokerMessage = new IncomingBrokerMessage($message->getBody());
            $this->entityManager->persist($brokerMessage);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            Debugger::log($e, Debugger::ERROR);
            return IConsumer::MESSAGE_NACK;
        }
        return IConsumer::MESSAGE_ACK;
    }



    public function processIncomingQueue()
    {
        if (!$this->incomingMessageProcessingBehavior) {
            throw new MissingIncomingMessageProcessingBehaviorClassException();
        }

        $messages = $this->entityManager->createQueryBuilder()
            ->select("m")
            ->from(IncomingBrokerMessage::class, "m")
            ->where("m.processedAt IS NULL")
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        foreach ($messages as $message) {
            yield "Processing " . $message->getId();
            $this->incomingMessageProcessingBehavior->apply($message);
        }

        yield "Finished.";
    }

}