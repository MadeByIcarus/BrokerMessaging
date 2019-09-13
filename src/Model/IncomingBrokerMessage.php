<?php
declare(strict_types=1);

namespace Icarus\BrokerMessaging\Model;


use Doctrine\ORM\Mapping as ORM;
use Icarus\DoctrineHelpers\Entities\Attributes\BigIdentifier;
use Icarus\RabbitMQ\Messages\JsonMessage;


/**
 * @ORM\Entity
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

}