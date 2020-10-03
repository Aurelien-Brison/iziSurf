<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;




class Contact
{
    /**
     * 
     */
    private $id;

    /**
     * @Assert\Type("string")
     * @Assert\Regex(pattern="/\d/", match=false)
     * @Assert\NotBlank(
     *  message = "Renseigne ton nom")
     */
    private $lastname;

    /**
     * @Assert\Type("string")
     * @Assert\Regex(pattern="/\d/", match=false)
     * @Assert\NotBlank(
     *  message = "Renseigne ton prénom")
     */
    private $firstname;

    /**
     * @Assert\Email()
     * @Assert\NotBlank(
     *  message = "Renseigne ton email")
     */
    private $email;

    private $object;

    /**
     * @Assert\NotBlank(
     *  message = "Décris ta demande")
     */
    private $message;

    /**
     * @var int
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
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

    public function getobject(): ?string
    {
        return $this->object;
    }

    public function setobject(string $object): self
    {
        $this->object = $object;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

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

    /*public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }*/

    /**
     * Get the value of date
     *
     * @return  int
     */ 
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set the value of date
     *
     * @param  int  $date
     *
     * @return  self
     */ 
    public function setDate(int $date)
    {
        $this->date = $date;

        return $this;
    }
}