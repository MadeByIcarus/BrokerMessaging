<?php
declare(strict_types=1);

namespace Icarus\BrokerMessaging;


use Icarus\BrokerMessaging\Model\IncomingBrokerMessage;
use Icarus\RabbitMQ\IConsumer;
use Icarus\RabbitMQ\IIncomingMessageHandler;
use Nettrine\ORM\EntityManagerDecorator;
use PhpAmqpLib\Message\AMQPMessage;
use Tracy\Debugger;


class IncomingMessageService implements IIncomingMessageHandler
{

    /**
     * @var EntityManagerDecorator
     */
    private $entityManager;

    /**
     * @var IIncomingMessageProcessor
     */
    private $incomingMessageProcessor;



    public function __construct(
        EntityManagerDecorator $entityManager,
        IIncomingMessageProcessor $incomingMessageProcessor = null
    )
    {
        $this->entityManager = $entityManager;
        $this->incomingMessageProcessor = $incomingMessageProcessor;
    }



    public function handle(AMQPMessage $message): int
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



    public function processMessages()
    {
        if (!$this->incomingMessageProcessor) {
            throw new MissingIncomingMessageProcessorException();
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
            $this->incomingMessageProcessor->process($message);
        }

        yield "Finished.";
    }

}