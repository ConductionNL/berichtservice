<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A  message to be send to a spefic recipient or list troug a message service.
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true},
 *     itemOperations={
 *          "get",
 *          "put",
 *          "delete",
 *          "get_change_logs"={
 *              "path"="/messages/{id}/change_log",
 *              "method"="get",
 *              "swagger_context" = {
 *                  "summary"="Changelogs",
 *                  "description"="Gets al the change logs for this resource"
 *              }
 *          },
 *          "get_audit_trail"={
 *              "path"="/messages/{id}/audit_trail",
 *              "method"="get",
 *              "swagger_context" = {
 *                  "summary"="Audittrail",
 *                  "description"="Gets the audit trail for this resource"
 *              }
 *          }
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 * @Gedmo\Loggable(logEntryClass="Conduction\CommonGroundBundle\Entity\ChangeLog")
 *
 * @ApiFilter(BooleanFilter::class)
 * @ApiFilter(OrderFilter::class)
 * @ApiFilter(DateFilter::class, strategy=DateFilter::EXCLUDE_NULL)
 * @ApiFilter(SearchFilter::class, properties={"type": "exact", "resource": "exact"})
 */
class Message
{
    /**
     * @var UuidInterface The UUID identifier of this resource
     *
     * @example e2984465-190a-4562-829e-a8cca81aa35d
     *
     * @Assert\Uuid
     * @Groups({"read"})
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var string Either a contact component person or contact list or an plain email that will recieve this message
     *
     * @example https://cc.zaakonline.nl/people/06cd0132-5b39-44cb-b320-a9531b2c4ac7
     *
     * @Gedmo\Versioned
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reciever;

    /**
     * @var string Either a contact component person, or wrc application or an plain email that sends this message
     *
     * @example https://cc.zaakonline.nl/people/06cd0132-5b39-44cb-b320-a9531b2c4ac7
     *
     * @Gedmo\Versioned
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sender;

    /**
     * @var string subject of the mail
     *
     * @example reset mail
     *
     * @Gedmo\Versioned
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @var string The webresource template object (from wrc) that is used as content for this message
     *
     * @example https://wrc.zaakonline.nl/templates/013276cc-1483-46b4-ad5b-1cba5acf6d9f
     *
     * @Gedmo\Versioned
     * @Groups({"read", "write"})
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @var string The type of this message.
     *
     * @example WarningMessageX
     *
     * @Gedmo\Versioned
     * @Assert\Length(
     *      max = 255
     * )
     * @Gedmo\Versioned
     * @Groups({"read","write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var string A resource used for this message.
     *
     * @example https://dev.zuid-drecht.nl/api/v1/uc/users/2881bb7a-4e1a-49ga-a824-af6c9917a3e5
     *
     * @Gedmo\Versioned
     * @Assert\Url
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resource;

    /**
     * @Gedmo\Versioned
     * @Groups({"read", "write"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @var string The current status of this message
     *
     * @example concept
     *
     * @Gedmo\Versioned
     * @Assert\Choice({"concept", "queued", "sending", "send", "delivered"})
     * @Assert\Length(
     *      max = 255
     * )
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @var The id of this message with the message service
     *
     * @example 013276cc-1483-46b4-ad5b-1cba5acf6d9f
     *
     * @Gedmo\Versioned
     * @Assert\Length(
     *      max = 255
     * )
     * @Groups({"read"})
     * @ORM\Column(name="external_service_id", type="string", length=255, nullable=true)
     */
    private $externalServiceId;

    /**
     * @var DateTime The moment this message was send
     *
     * @Gedmo\Versioned
     * @Groups({"read"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $send;

    /**
     * @var Service The service used to send this message
     *
     * @MaxDepth(1)
     * @Groups({"read", "write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Service", inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $service;

    /**
     * @var Datetime The moment this request was created
     *
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @var Datetime The moment this request last Modified
     *
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateModified;

    /**
     * @Groups({"read","write"})
     * @ORM\OneToMany(targetEntity=Attachment::class, mappedBy="message", cascade={"persist", "remove"})
     */
    private $attachments;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getReciever(): ?string
    {
        return $this->reciever;
    }

    public function setReciever(?string $reciever): self
    {
        $this->reciever = $reciever;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(?string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setResource(string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getExternalServiceId(): ?string
    {
        return $this->externalServiceId;
    }

    public function setExternalServiceId(string $externalServiceId): self
    {
        $this->externalServiceId = $externalServiceId;

        return $this;
    }

    public function getSend(): ?\DateTimeInterface
    {
        return $this->send;
    }

    public function setSend(\DateTimeInterface $send): self
    {
        $this->send = $send;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateModified(): ?\DateTimeInterface
    {
        return $this->dateModified;
    }

    public function setDateModified(\DateTimeInterface $dateModified): self
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * @return Collection|Attachment[]
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(Attachment $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments[] = $attachment;
            $attachment->setMessage($this);
        }

        return $this;
    }

    public function removeAttachment(Attachment $attachment): self
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
            // set the owning side to null (unless already changed)
            if ($attachment->getMessage() === $this) {
                $attachment->setMessage(null);
            }
        }

        return $this;
    }
}
