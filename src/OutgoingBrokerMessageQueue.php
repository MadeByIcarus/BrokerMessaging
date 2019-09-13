<?php
declare(strict_types=1);

namespace Icarus\BrokerMessaging;


use Icarus\RabbitMQ\IConfirmHandler;
use Icarus\BrokerMessaging\Model\OutgoingBrokerMessage;
use Icarus\RabbitMQ\Messages\AMQPMessageFactory;
use Icarus\RabbitMQ\Messages\JsonMessage;
use Icarus\RabbitMQ\RabbitMQ;
use Nettrine\ORM\EntityManagerDecorator;
use PhpAmqpLib\Message\AMQPMessage;


class OutgoingBrokerMessageQueue implements IConfirmHandler
{

    /**
     * @var EntityManagerDecorator
     */
    private $entityManager;

    /**
     * @var RabbitMQ
     */
    private $rabbitMQ;



    public function __construct(EntityManagerDecorator $entityManager, RabbitMQ $rabbitMQ)
    {
        $this->entityManager = $entityManager;
        $this->rabbitMQ = $rabbitMQ;
    }



    public function queueJsonMessage(string $producerName, JsonMessage $jsonMessage, bool $doFlush = false)
    {
        $brokerMessage = new OutgoingBrokerMessage($producerName, (string)$jsonMessage);
        $this->entityManager->persist($brokerMessage);
        $doFlush && $this->entityManager->flush();
    }



    public function publishQueuedMessages($limit = 50)
    {
        /** @var OutgoingBrokerMessage[] $queuedMessages */
        $queuedMessages = $this->entityManager->getRepository(OutgoingBrokerMessage::class)
            ->findBy(['waitingForAck' => false], [], $limit);

        $producers = [];

        foreach ($queuedMessages as $brokerMessage) {
            $producers[] = $producer = $this->rabbitMQ->getProducer($brokerMessage->getProducerName());

            $jsonMessage = JsonMessage::fromJson($brokerMessage->getMessage());
            $jsonMessage->setMsgId($brokerMessage->getId());

            $AMQPMessage = AMQPMessageFactory::createJsonMessage($jsonMessage);

            $producer->addToBatch($AMQPMessage);

            $brokerMessage->setWaitingForAck(true);
            $this->entityManager->persist($brokerMessage);
        }

        $this->entityManager->flush();

        foreach ($producers as $producer) {
            $producer->publishBatch();
        }
    }



    public function requeueLongWaitingMessages(int $timeout = 90)
    {
        $date = new \DateTime();
        $date->modify("-$timeout seconds");

        $this->entityManager->createQueryBuilder()
            ->update(OutgoingBrokerMessage::class)
            ->set("waitingForAck", false)
            ->where("createdAt <= :date AND waitingForAck = :waiting AND ack = :ack AND nack = :nack")
            ->setParameters([
                'date' => $date,
                'waiting' => true,
                'ack' => false,
                'nack' => false,
            ])
            ->getQuery()
            ->execute();
    }



    // IConfirmHandler methods

    public function handleAck(AMQPMessage $message)
    {
        $this->processAMQPMessage($message, true);
    }



    public function handleNack(AMQPMessage $message)
    {
        $this->processAMQPMessage($message, false);
    }



    private function processAMQPMessage(AMQPMessage $AMQPMessage, bool $isAck)
    {
        $jsonMessage = JsonMessage::fromJson($AMQPMessage->getBody());
        $id = $jsonMessage->getMsgId();

        /** @var OutgoingBrokerMessage $brokerMessage */
        $brokerMessage = $this->entityManager->find(OutgoingBrokerMessage::class, $id);
        if ($isAck) {
            $brokerMessage->setAck(true);
        } else {
            $brokerMessage->setNack(true);
        }
        $this->entityManager->persist($brokerMessage);
        $this->entityManager->flush();
    }
}