<?php
declare(strict_types=1);

namespace Icarus\BrokerMessaging\Model;


use Doctrine\ORM\Mapping as ORM;
use Icarus\DoctrineHelpers\Entities\Attributes\BigIdentifier;
use Icarus\RabbitMQ\Messages\JsonMessage;


/**
 * @ORM\Entity
 * @ORM\Table(name="broker_message_outgoing")
 */
class OutgoingBrokerMessage
{

    use BigIdentifier;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $producerName;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $type;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    private $message;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $waitingForAck = false;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $ack = false;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $nack = false;



    public function __construct(string $producerName, string $message)
    {
        $this->createdAt = new \DateTime();
        $this->producerName = $producerName;
        $this->message = $message;
        $this->extractRelevantValues($message);
    }



    private function extractRelevantValues(string $json)
    {
        $message = JsonMessage::fromJson($json);
        $this->type = $message->getType();
    }



    /**
     * @return string
     */
    public function getProducerName(): string
    {
        return $this->producerName;
    }



    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }



    public function isWaitingForAck(): bool
    {
        return $this->waitingForAck;
    }



    public function setWaitingForAck(bool $waitingForAck): void
    {
        $this->waitingForAck = $waitingForAck;
    }



    public function isAck(): bool
    {
        return $this->ack;
    }



    public function setAck(bool $ack): void
    {
        $this->ack = $ack;
        $this->setWaitingForAck(false);
    }



    public function isNack(): bool
    {
        return $this->nack;
    }



    public function setNack(bool $nack): void
    {
        $this->nack = $nack;
    }

}