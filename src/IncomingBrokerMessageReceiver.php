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



    public function saveIncomingMessage(AMQPMessage $AMQPMessage, bool $doFlush = true)
    {
        $message = new IncomingBrokerMessage($AMQPMessage->getBody());
        $this->entityManager->persist($message);

        $doFlush && $this->entityManager->flush();
    }



    public function process()
    {
        if (!$this->incomingMessageProcessor) {
            throw new MissingIncomingMessageProcessorClassException();
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