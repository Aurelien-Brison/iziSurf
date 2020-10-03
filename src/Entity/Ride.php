<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Fit;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity(repositoryClass="App\Repository\RideRepository")
 * @ApiResource(attributes={
 *      "normalization_context"={"groups"={"normaliz:ride"}}
 * })
 */
class Ride
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("normaliz:ride")
     * @Groups("normaliz:fit")
     * @Groups("normaliz:myride")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride")
     * @Assert\NotBlank(
     *  message = "Renseigne ta ville de départ")
     */
    private $cityDeparture;

    /**
     * @ORM\Column(type="string", length=150)
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride")
     * @Assert\NotBlank(
     *  message = "Renseigne ton lieu de départ")
     */
    private $placeDeparture;

    /**
     * @ORM\Column(type="date")
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride") 
     * @Assert\NotBlank(
     *  message = "Renseigne ta date de départ")
     * @Assert\GreaterThan(
     *  "yesterday",
     *   message = "La date ne peut pas être inférieure à la date d'aujourd'hui")
     */
    private $departureDate;

    /**
     * @ORM\Column(type="time")
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride") 

     * @Assert\NotBlank(
     *  message = "Renseigne une heure de départ")
     * 
     */
    private $departureHour;

    /**
     * @ORM\Column(type="date")
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride") 

     */
    private $returnDate;

    /**
     * @ORM\Column(type="time", nullable=true)
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride")

     */
    private $returnHour;

    /**
     * @ORM\Column(type="integer")
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride") 

     * @Assert\NotBlank(
     *  message = "Renseigne le nombre de passagers que tu peux prendre"
     * )
     * @Assert\Range(
     *      min = 0,
     *      max = 50
     * )
     */
    private $availableSeat;

    /**
     * @ORM\Column(type="integer")
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride") 

     * @Assert\NotBlank(
     *  message = "Renseigne le nombre de planches que tu peux transporter"
     * )
     * @Assert\Range(
     *      min = 0,
     *      max = 50
     * )
     */
    private $boardMax;

    /**
     * @ORM\Column(type="decimal", precision=3, scale=1)
     * @Assert\Range(
     *      min = 0,
     *      max = 15
     * )
     * @Assert\NotBlank(
     *  message = "Renseigne la taille maximale des planches que tu peux transporter"
     * )
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride")

     */
    private $boardSizeMax;

    /**
     * @ORM\Column(type="integer")
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride") 

     */
    private $isSameGender;

    /**
     * @ORM\Column(type="decimal", scale=2)
     * @Assert\Range(
     *      min = 0,
     *      max = 1000
     * )
     * @Assert\NotBlank(
     *  message = "Renseigne un prix")
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride")
     */
    private $price;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups("normaliz:ride")
     */
    private $rideDescription;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $cityLatitude;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $cityLongitude;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Car", inversedBy="rides", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups("normaliz:ride") 
     * 
     */
    private $car;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Fit", mappedBy="ride")
     * @Groups("normaliz:ride")
     */
    private $fits;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="ride", orphanRemoval=true)
     */
    private $messages;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="rides")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride") 

     */
    private $driver;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Spot", inversedBy="rides")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("normaliz:ride")
     * @Groups("normaliz:myride") 

     * @Assert\NotBlank(
     *  message = "Sélectionne un spot dans la liste")
     */
    private $spot;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Notification", mappedBy="ride", orphanRemoval=true)
     */
    private $notifications;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"cityDeparture", "placeDeparture", "id"})
     * @Groups("normaliz:myride")
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Search", mappedBy="ride")
     */
    private $searches;

    /**
     * @ORM\Column(type="boolean")
     */
    private $completed;

    public function __construct()
    {
        $this->fits = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->messages = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->searches = new ArrayCollection();
        $this->completed = 0;
    }

    // public function __toString(){
    //     $this->id . ' ' . $this->spot.' '.$this->createdAt;
    //     $this->id . ' ' . $this->cityDeparture.' '.$this->placeDeparture;
    //     return $this;
    // }

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

    public function getPlaceDeparture(): ?string
    {
        return $this->placeDeparture;
    }

    public function setPlaceDeparture(string $placeDeparture): self
    {
        $this->placeDeparture = $placeDeparture;

        return $this;
    }

    public function getDepartureDate(): ?\DateTimeInterface
    {
        return $this->departureDate;
    }

    public function setDepartureDate($departureDate): self
    {
        $this->departureDate = $departureDate;

        return $this;
    }

    public function getdepartureHour(): ?\DateTimeInterface
    {
        return $this->departureHour;
    }

    public function setdepartureHour($departureHour): self
    {
        $this->departureHour = $departureHour;

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

    public function getReturnHour(): ?\DateTimeInterface
    {
        return $this->returnHour;
    }

    public function setReturnHour(?\DateTimeInterface $returnHour): self
    {
        $this->returnHour = $returnHour;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

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

    public function getRideDescription(): ?string
    {
        return $this->rideDescription;
    }

    public function setRideDescription(?string $rideDescription): self
    {
        $this->rideDescription = $rideDescription;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
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

    /**
     * Get the value of cityLatitude
     */ 
    public function getCityLatitude()
    {
        return $this->cityLatitude;
    }

    /**
     * Set the value of cityLatitude
     *
     * @return  self
     */ 
    public function setCityLatitude($cityLatitude)
    {
        $this->cityLatitude = $cityLatitude;

        return $this;
    }

    /**
     * Get the value of cityLongitude
     */ 
    public function getCityLongitude()
    {
        return $this->cityLongitude;
    }

    /**
     * Set the value of cityLongitude
     *
     * @return  self
     */ 
    public function setCityLongitude($cityLongitude)
    {
        $this->cityLongitude = $cityLongitude;

        return $this;
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

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): self
    {
        $this->car = $car;

        return $this;
    }

    /**
     * @return Collection|Fit[]
     */
    public function getfits(): Collection
    {
        return $this->fits;
    }

    public function addFit(Fit $fit): self
    {
        if (!$this->fits->contains($fit)) {
            $this->fits[] = $fit;
            $fit->setRide($this);
        }

        return $this;
    }

    public function removeFit(Fit $fit): self
    {
        if ($this->fits->contains($fit)) {
            $this->fits->removeElement($fit);
            // set the owning side to null (unless already changed)
            if ($fit->getRide() === $this) {
                $fit->setRide(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setRide($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getRide() === $this) {
                $message->setRide(null);
            }
        }

        return $this;
    }

    public function getDriver(): ?User
    {
        return $this->driver;
    }

    public function setDriver(?User $driver): self
    {
        $this->driver = $driver;

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

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setRide($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
            // set the owning side to null (unless already changed)
            if ($notification->getRide() === $this) {
                $notification->setRide(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of slug
     */ 
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the value of slug
     *
     * @return  self
     */ 
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|Search[]
     */
    public function getSearches(): Collection
    {
        return $this->searches;
    }

    public function addSearch(Search $search): self
    {
        if (!$this->searches->contains($search)) {
            $this->searches[] = $search;
            $search->setRide($this);
        }

        return $this;
    }

    public function removeSearch(Search $search): self
    {
        if ($this->searches->contains($search)) {
            $this->searches->removeElement($search);
            // set the owning side to null (unless already changed)
            if ($search->getRide() === $this) {
                $search->setRide(null);
            }
        }

        return $this;
    }

    public function getCompleted(): ?bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }
}
