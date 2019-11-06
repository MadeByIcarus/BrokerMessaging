<?php
declare(strict_types=1);

namespace Icarus\BrokerMessaging\Model;


use Doctrine\ORM\Mapping as ORM;
use Icarus\DoctrineHelpers\Entities\Attributes\BigIdentifier;
use Icarus\RabbitMQ\Messages\JsonMessage;


/**
 * @ORM\Entity
 * @ORM\Table(name="broker_message_incoming")
 */
class IncomingBrokerMessage
{

    use BigIdentifier;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     * @var
     */
    private $originId;

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
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    private $processedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    private $processingFailedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $errorMessage;



    public function __construct(string $message)
    {
        $this->createdAt = new \DateTime();
        $this->message = $message;
        $this->extractRelevantValues($message);
    }



    private function extractRelevantValues(string $json)
    {
        $message = JsonMessage::fromJson($json);
        $this->originId = $message->getMsgId();
        $this->type = $message->getType();
    }



    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }



    /**
     * @return mixed
     */
    public function getOriginId()
    {
        return $this->originId;
    }



    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }



    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }



    /**
     * @return \DateTime|null
     */
    public function getProcessedAt()
    {
        return $this->processedAt;
    }



    /**
     * @return \DateTime|null
     */
    public function getProcessingFailedAt()
    {
        return $this->processingFailedAt;
    }



    /**
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

}