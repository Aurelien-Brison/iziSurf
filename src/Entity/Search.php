<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SearchRepository")
 */
class Search
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $cityDeparture;

    /**
     * @ORM\Column(type="date")
     */
    private $departureDate;

    /**
     * @ORM\Column(type="date")
     */
    private $returnDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $availableSeat;

    /**
     * @ORM\Column(type="integer")
     */
    private $boardMax;

    /**
     * @ORM\Column(type="decimal", precision=3, scale=1)
     */
    private $boardSizeMax;

    /**
     * @ORM\Column(type="integer")
     */
    private $isSameGender;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $cityLatitude;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $cityLongitude;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isNotifiedWhenResult;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Spot", inversedBy="searches")
     * @ORM\JoinColumn(nullable=false)
     */
    private $spot;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="searches", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ride", inversedBy="searches")
     */
    private $ride;

    public function __construct()
    {
        $this->isNotifiedWhenResult = 0;
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCityDeparture(): ?string
    {
        return $this->cityDeparture;
    }

    public function setCityDeparture(string $cityDeparture): self
    {
        $this->cityDeparture = $cityDeparture;

        return $this;
    }

    public function getDepartureDate(): ?\DateTimeInterface
    {
        return $this->departureDate;
    }

    public function setDepartureDate(\DateTimeInterface $departureDate): self
    {
        $this->departureDate = $departureDate;

        return $this;
    }

    public function getReturnDate(): ?\DateTimeInterface
    {
        return $this->returnDate;
    }

    public function setReturnDate($returnDate): self
    {
        $this->returnDate = $returnDate;

        return $this;
    }

    public function getAvailableSeat(): ?int
    {
        return $this->availableSeat;
    }

    public function setAvailableSeat(int $availableSeat): self
    {
        $this->availableSeat = $availableSeat;

        return $this;
    }

    public function getBoardMax(): ?int
    {
        return $this->boardMax;
    }

    public function setBoardMax(int $boardMax): self
    {
        $this->boardMax = $boardMax;

        return $this;
    }

    public function getBoardSizeMax(): ?string
    {
        return $this->boardSizeMax;
    }

    public function setBoardSizeMax(string $boardSizeMax): self
    {
        $this->boardSizeMax = $boardSizeMax;

        return $this;
    }

    public function getIsSameGender(): ?int
    {
        return $this->isSameGender;
    }

    public function setIsSameGender(int $isSameGender): self
    {
        $this->isSameGender = $isSameGender;

        return $this;
    }

    public function getCityLatitude(): ?string
    {
        return $this->cityLatitude;
    }

    public function setCityLatitude(string $cityLatitude): self
    {
        $this->cityLatitude = $cityLatitude;

        return $this;
    }

    public function getCityLongitude(): ?string
    {
        return $this->cityLongitude;
    }

    public function setCityLongitude(string $cityLongitude): self
    {
        $this->cityLongitude = $cityLongitude;

        return $this;
    }

    public function getIsNotifiedWhenResult(): ?bool
    {
        return $this->isNotifiedWhenResult;
    }

    public function setIsNotifiedWhenResult(bool $isNotifiedWhenResult): self
    {
        $this->isNotifiedWhenResult = $isNotifiedWhenResult;

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

    public function getSpot(): ?Spot
    {
        return $this->spot;
    }

    public function setSpot(?Spot $spot): self
    {
        $this->spot = $spot;

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
}
