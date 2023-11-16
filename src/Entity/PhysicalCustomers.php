<?php

namespace App\Entity;

use App\Repository\PhysicalCustomersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;



#[ORM\Entity(repositoryClass: PhysicalCustomersRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Un compte utilisateur est déjà associé avec cette email, veuillez en choisir un autre.')]
class PhysicalCustomers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'physicalCustomers')]
    private ?Organization $organization = null;


    #[Assert\NotBlank(message:"Ce champs est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le prénom ne doit pas dépasser {{ limit }} caractères',
    )]
    #[Assert\Regex(
        pattern: '/^[0-9a-zA-Z-_ áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+$/i',
        match: true,
        message: 'Le prénom doit contenir uniquement des lettres, des chiffres le tiret du milieu de l\'undescore.',
    )]
    #[ORM\Column(length: 255)]
    private ?string $firstName = null;


    #[Assert\NotBlank(message:"Ce champs est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères',
        )]
    #[Assert\Regex(
        pattern: '/^[0-9a-zA-Z-_ áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+$/i',
        match: true,
        message: 'Le nom doit contenir uniquement des lettres, des chiffres le tiret du milieu de l\'undescore.',
        )]
    #[ORM\Column(length: 255)]
    private ?string $lastName = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateBirthday = null;

      
    #[Assert\NotNull(message:"Vous devez faire un choix dans la liste.")]
    #[ORM\Column(length: 255)]
    private ?string $gender = null;



    #[Assert\NotBlank(message:"Ce champs est obligatoire.")]
    #[Assert\Length(
        max: 180,
        maxMessage: 'L\'email ne doit pas dépasser {{ limit }} caractères',
    )]
    #[Assert\Email(
        message: 'l\'email {{ value }} n\'est pas valide.',
    )]
    #[ORM\Column(length: 180, unique:true)]
    private ?string $email = null;



    #[Assert\Regex(
        pattern: "/^\d+\s[0-9A-Za-zÀ-ÖØ-öø-ÿ\s'-]+$/",
        match: true,
        message: 'l\'adresse peut contenir seulement des lettres majuscules, des lettres minuscules et des caractères spéciaux et numéro de la rue.',
        )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'adresse ne doit pas dépasser {{ limit }} caractères',
        )]
    #[Assert\NotBlank(message:"Ce champs est obligatoire.")]
    #[ORM\Column(length: 255)]
    private ?string $address = null;


    #[Assert\Length(
        max: 20,
        maxMessage: 'Le code postal ne doit pas dépasser {{ limit }} caractères',
        )]
    #[Assert\Regex(
        pattern: '/^(?:0[1-9]|[1-8]\d|9[0-8])\d{3}$/',
        match: true,
        message: 'Le code postal doit contenir que des chiffres.',
    )]
    #[Assert\NotBlank(message:"Ce champs est obligatoire.")]
    #[ORM\Column(length: 20)]
    private ?string $zip = null;


    #[Assert\Regex(
        pattern: '/^[a-zA-Z-_ áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+$/i',
        match: true,
        message: 'Le nom de la ville doit contenir uniquement des lettres et le tiret du milieu de l\'undescore.',
        )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom de la ville ne doit pas dépasser {{ limit }} caractères',
        )]
    #[Assert\NotBlank(message:"Ce champs est obligatoire.")]
    #[ORM\Column(length: 255)]
    private ?string $city = null;


    #[Assert\Regex(
        pattern: '/^[a-zA-Z-_ áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+$/i',
        match: true,
        message: 'Le nom du Pays doit contenir uniquement des lettres et le tiret du milieu de l\'undescore.',
        )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom du Pays ne doit pas dépasser {{ limit }} caractères',
    )]
    #[Assert\NotBlank(message:"Ce champs est obligatoire.")]
    #[ORM\Column(length: 255)]
    private ?string $country = null;


    #[Assert\NotNull(message:"Indiquez si vous demeuré à l'adresse inscrite.")]
    #[ORM\Column]
    private ?bool $isNPAI = null;


    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;


    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'physicalCustomer', targetEntity: Donations::class)]
    private Collection $donations;

    public function __construct()
    {
        $this->donations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): static
    {
        $this->organization = $organization;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getDateBirthday(): ?\DateTimeInterface
    {
        return $this->dateBirthday;
    }

    public function setDateBirthday(\DateTimeInterface $dateBirthday): static
    {
        $this->dateBirthday = $dateBirthday;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(string $zip): static
    {
        $this->zip = $zip;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function isIsNPAI(): ?bool
    {
        return $this->isNPAI;
    }

    public function setIsNPAI(bool $isNPAI): static
    {
        $this->isNPAI = $isNPAI;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, Donations>
     */
    public function getDonations(): Collection
    {
        return $this->donations;
    }

    public function addDonation(Donations $donation): static
    {
        if (!$this->donations->contains($donation)) {
            $this->donations->add($donation);
            $donation->setPhysicalCustomer($this);
        }

        return $this;
    }

    public function removeDonation(Donations $donation): static
    {
        if ($this->donations->removeElement($donation)) {
            // set the owning side to null (unless already changed)
            if ($donation->getPhysicalCustomer() === $this) {
                $donation->setPhysicalCustomer(null);
            }
        }

        return $this;
    }
}
