<?php

namespace App\Entity;

use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
class Organization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


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
    private ?string $organizationName = null;


    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;


    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Relation::class, orphanRemoval: true)]
    private Collection $relations;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: PhysicalCustomers::class, orphanRemoval: true)]
    private Collection $physicalCustomers;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: MoralCustomers::class, orphanRemoval: true)]
    private Collection $moralCustomers;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Project::class, orphanRemoval: true)]
    private Collection $projects;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $isAdmin = 0;

    public function __construct()
    {
        $this->relations = new ArrayCollection();
        $this->physicalCustomers = new ArrayCollection();
        $this->moralCustomers = new ArrayCollection();
        $this->projects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganizationName(): ?string
    {
        return $this->organizationName;
    }

    public function setOrganizationName(string $organizationName): static
    {
        $this->organizationName = $organizationName;

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
     * @return Collection<int, Relation>
     */
    public function getRelations(): Collection
    {
        return $this->relations;
    }

    public function addRelation(Relation $relation): static
    {
        if (!$this->relations->contains($relation)) {
            $this->relations->add($relation);
            $relation->setOrganization($this);
        }

        return $this;
    }

    public function removeRelation(Relation $relation): static
    {
        if ($this->relations->removeElement($relation)) {
            // set the owning side to null (unless already changed)
            if ($relation->getOrganization() === $this) {
                $relation->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PhysicalCustomers>
     */
    public function getPhysicalCustomers(): Collection
    {
        return $this->physicalCustomers;
    }

    public function addPhysicalCustomer(PhysicalCustomers $physicalCustomer): static
    {
        if (!$this->physicalCustomers->contains($physicalCustomer)) {
            $this->physicalCustomers->add($physicalCustomer);
            $physicalCustomer->setOrganization($this);
        }

        return $this;
    }

    public function removePhysicalCustomer(PhysicalCustomers $physicalCustomer): static
    {
        if ($this->physicalCustomers->removeElement($physicalCustomer)) {
            // set the owning side to null (unless already changed)
            if ($physicalCustomer->getOrganization() === $this) {
                $physicalCustomer->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MoralCustomers>
     */
    public function getMoralCustomers(): Collection
    {
        return $this->moralCustomers;
    }

    public function addMoralCustomer(MoralCustomers $moralCustomer): static
    {
        if (!$this->moralCustomers->contains($moralCustomer)) {
            $this->moralCustomers->add($moralCustomer);
            $moralCustomer->setOrganization($this);
        }

        return $this;
    }

    public function removeMoralCustomer(MoralCustomers $moralCustomer): static
    {
        if ($this->moralCustomers->removeElement($moralCustomer)) {
            // set the owning side to null (unless already changed)
            if ($moralCustomer->getOrganization() === $this) {
                $moralCustomer->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setOrganization($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getOrganization() === $this) {
                $project->setOrganization(null);
            }
        }

        return $this;
    }

    public function getIsAdmin(): ?int
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(int $isAdmin): static
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }
}
