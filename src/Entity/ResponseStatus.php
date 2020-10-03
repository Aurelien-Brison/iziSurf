<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ResponseStatusRepository")
*  @ApiResource(attributes={
 *      "denormalization_context"={"groups"={"denormaliz:status"}}
 * })
 */
class ResponseStatus
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("normaliz:fit")
     * @Groups("normaliz:myride")
     * @Groups("normaliz:ride")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups("normaliz:ride")
     * @Groups("denormaliz:status")
     * @Groups("normaliz:fit")
     * @Groups("normaliz:myride")     
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Groups("denormaliz:status")
     */
    private $orderResponseStatus;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("denormaliz:status")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $upadatedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Fit", mappedBy="status")
     */
    private $fits;

    public function __construct()
    {
        $this->fits = new ArrayCollection();
        $this->createdAt = new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOrderResponseStatus(): ?int
    {
        return $this->orderResponseStatus;
    }

    public function setOrderResponseStatus(int $orderResponseStatus): self
    {
        $this->orderResponseStatus = $orderResponseStatus;

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

    public function getUpadatedAt(): ?\DateTimeInterface
    {
        return $this->upadatedAt;
    }

    public function setUpadatedAt(?\DateTimeInterface $upadatedAt): self
    {
        $this->upadatedAt = $upadatedAt;

        return $this;
    }

    /**
     * @return Collection|Fit[]
     */
    public function getFits(): Collection
    {
        return $this->fits;
    }

    public function addFit(Fit $fit): self
    {
        if (!$this->fits->contains($fit)) {
            $this->fits[] = $fit;
            $fit->setStatus($this);
        }

        return $this;
    }

    public function removeFit(Fit $fit): self
    {
        if ($this->fits->contains($fit)) {
            $this->fits->removeElement($fit);
            // set the owning side to null (unless already changed)
            if ($fit->getStatus() === $this) {
                $fit->setStatus(null);
            }
        }

        return $this;
    }
}
