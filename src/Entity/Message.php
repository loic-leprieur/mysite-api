<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource]
class Message
{
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "IDENTITY")]
    private int $id = 0;

    #[ORM\Column(length: 255)]
    #[NotNull]
    private string $email;

    #[ORM\Column(length: 255)]
    #[NotNull]
    private string $expediteur;

    #[ORM\Column(length: 800)]
    #[NotNull]
    private string $message;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[NotNull]
    private string $created_at;

    public function __construct()
    {
        $this->created_at = date('m/d/Y h:i:s a', time());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getExpediteur(): ?string
    {
        return $this->expediteur;
    }

    public function setExpediteur(string $expediteur): self
    {
        $this->expediteur = $expediteur;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }
}
