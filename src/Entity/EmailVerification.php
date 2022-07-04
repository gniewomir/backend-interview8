<?php

namespace App\Entity;

use App\Repository\EmailVerificationRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: EmailVerificationRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class EmailVerification implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Email::class, inversedBy: 'emailVerifications')]
    #[ORM\JoinColumn(nullable: false)]
    private $email;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'string', length: 40)]
    private $result;

    #[ORM\Column(type: 'boolean')]
    private $isPrivate;

    #[ORM\Column(type: 'boolean')]
    private $isCatchall;

    #[ORM\Column(type: 'boolean')]
    private $isDisposable;

    #[ORM\Column(type: 'boolean')]
    private $isFreemail;

    #[ORM\Column(type: 'boolean')]
    private $isRolebased;

    #[ORM\Column(type: 'boolean')]
    private $isDnsValidMx;

    #[ORM\Column(type: 'boolean')]
    private $isSmtpValid;

    #[ORM\PrePersist]
    public function prePersist()
    {
        if (empty($this->createdAt)) {
            $this->createdAt = new DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?Email
    {
        return $this->email;
    }

    public function setEmail(?Email $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(string $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function isCatchall(): ?bool
    {
        return $this->isCatchall;
    }

    public function setIsCatchall(bool $isCatchall): self
    {
        $this->isCatchall = $isCatchall;

        return $this;
    }

    public function isDisposable(): ?bool
    {
        return $this->isDisposable;
    }

    public function setIsDisposable(bool $isDisposable): self
    {
        $this->isDisposable = $isDisposable;

        return $this;
    }

    public function isFreemail(): ?bool
    {
        return $this->isFreemail;
    }

    public function setIsFreemail(bool $isFreemail): self
    {
        $this->isFreemail = $isFreemail;

        return $this;
    }

    public function isRolebased(): ?bool
    {
        return $this->isRolebased;
    }

    public function setIsRolebased(bool $isRolebased): self
    {
        $this->isRolebased = $isRolebased;

        return $this;
    }

    public function isDnsValidMx(): ?bool
    {
        return $this->isDnsValidMx;
    }

    public function setIsDnsValidMx(bool $isDnsValidMx): self
    {
        $this->isDnsValidMx = $isDnsValidMx;

        return $this;
    }

    public function isSmtpValid(): ?bool
    {
        return $this->isSmtpValid;
    }

    public function setIsSmtpValid(bool $isSmtpValid): self
    {
        $this->isSmtpValid = $isSmtpValid;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'result' => $this->getResult(),
            'isPrivate' => $this->isPrivate(),
            'isCatchall' => $this->isCatchall(),
            'isDisposable' => $this->isDisposable(),
            'isFreemail' => $this->isFreemail(),
            'isRolebase' => $this->isRolebased(),
            'isDnsValidMx' => $this->isDnsValidMx(),
            'isSmtpValid' => $this->isSmtpValid(),
        ];
    }
}
