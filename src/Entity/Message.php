<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message
{
    /**
     * @var string[]
     */
    const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_SUCCESS,
        self::STATUS_ERROR,
    ];

    /**
     * @var string
     */
    const STATUS_ERROR = 'error';

    /**
     * @var string
     */
    const STATUS_NEW = 'new';

    /**
     * @var string
     */
    const STATUS_SUCCESS = 'success';

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $created;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $deliveryTime;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $error;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="uuid", unique=true)
     * @JMS\Type("string")
     */
    private $idempotencyKey;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $restaurantTitle;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Exclude
     */
    private $sent;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * Message constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->created = new DateTime();
        $this->status = self::STATUS_NEW;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    /**
     * @return \DateTime|null
     */
    public function getDeliveryTime(): ?\DateTime
    {
        return $this->deliveryTime;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getIdempotencyKey(): ?string
    {
        return $this->idempotencyKey;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string|null
     */
    public function getRestaurantTitle(): ?string
    {
        return $this->restaurantTitle;
    }

    /**
     * @return \DateTime|null
     */
    public function getSent(): ?\DateTime
    {
        return $this->sent;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param \DateTime|null $created
     *
     * @return self
     */
    public function setCreated(?\DateTime $created = null): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @param \DateTime|null $deliveryTime
     *
     * @return self
     */
    public function setDeliveryTime(?\DateTime $deliveryTime = null): self
    {
        $this->deliveryTime = $deliveryTime;

        return $this;
    }

    /**
     * @param string|null $error
     *
     * @return self
     */
    public function setError(?string $error = null): self
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @param string|null $idempotencyKey
     *
     * @return self
     */
    public function setIdempotencyKey(?string $idempotencyKey = null): self
    {
        $this->idempotencyKey = $idempotencyKey;

        return $this;
    }

    /**
     * @param string|null $phone
     *
     * @return self
     */
    public function setPhone(?string $phone = null): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @param string|null $restaurantTitle
     *
     * @return self
     */
    public function setRestaurantTitle(?string $restaurantTitle = null): self
    {
        $this->restaurantTitle = $restaurantTitle;

        return $this;
    }

    /**
     * @param \DateTime|null $sent
     *
     * @return self
     */
    public function setSent(?\DateTime $sent = null): self
    {
        $this->sent = $sent;

        return $this;
    }

    /**
     * @param string|null $status
     *
     * @return self
     */
    public function setStatus(?string $status = null): self
    {
        $this->status = $status;

        return $this;
    }
}
