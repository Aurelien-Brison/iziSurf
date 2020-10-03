<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;



/**
 * @ORM\Entity(repositoryClass="App\Repository\FitRepository")
 * @ApiResource(attributes={
 *      "normalization_context"={"groups"={"normaliz:fit"}}
 * })
 */
class Fit
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("normaliz:fit")
     * @Groups("normaliz:myride")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("normaliz:ride")
     * @Groups("normaliz:fit")
     * @Groups("normaliz:myride")
     */
    private $isFavorite;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("normaliz:fit")
     * @Groups("normaliz:myride")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("normaliz:fit")
     * @Groups("normaliz:myride")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="fits")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("normaliz:ride")
     * @Groups("normaliz:fit")
     * @Groups("normaliz:myride")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ride", inversedBy="fits", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups("normaliz:fit")
     * @Groups("normaliz:myride")
     */
    private $ride;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ResponseStatus", inversedBy="fits")
     * @ORM\JoinColumn(nullable=true)
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride")
     */
    private $status;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numberPlacesRequested;


    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsFavorite(): ?bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(bool $isFavorite): self
    {
        $this->isFavorite = $isFavorite;

        return $this;
    }


    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRide(): ?Ride
    {
        return $this->ride;
    }

    public function setRide(?Ride $ride): self
    {
        $this->ride = $ride;

        return $this;
    }

    public function getstatus(): ?ResponseStatus
    {
        return $this->status;
    }

    public function setstatus(?ResponseStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getNumberPlacesRequested(): ?int
    {
        return $this->numberPlacesRequested;
    }

    public function setNumberPlacesRequested(?int $numberPlacesRequested): self
    {
        $this->numberPlacesRequested = $numberPlacesRequested;

        return $this;
    }

}
