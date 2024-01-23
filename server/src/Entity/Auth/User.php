<?php

namespace App\Entity\Auth;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\UserProviderController as ControllerUserProviderController;
use App\Entity\Appointment;
use App\Entity\Blog\Comment;
use App\Entity\Blog\Publication;
use App\Entity\Employee;
use App\Entity\Establishment;
use App\Entity\PlanningEmployee;
use App\Entity\Provider;
use App\Entity\ProvisionEmployee;
use App\Entity\Shop\Product;
use App\Filters\CustomSearchFilter;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

//#[isGranted('ROLE_ADMIN')]
#[ApiResource(
    denormalizationContext: ['groups' => ['user:write']],
    normalizationContext: ['groups' => ['user:read']],
    operations: [
        new GetCollection(),
        new Post(),
        new Get(normalizationContext: ['groups' => ['user:read', 'user:read:full']], security: 'is_granted("VIEW", object)',),
        new Patch(denormalizationContext: ['groups' => ['user:write:update']], /*security: 'is_granted("EDIT", object)',*/),
    ],
)]
#[ORM\Table(name: '`user`')]
#[ORM\Entity()]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Auth;

    #[ApiFilter(CustomSearchFilter::class)]
    #[Groups(['user:write:update'])]
    #[Assert\Length(min: 2)]
    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ApiFilter(DateFilter::class)]
    #[Groups(['user:read'])]
    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Publication::class)]
    private Collection $posts;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'buyers')]
    private Collection $products;

    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(length: 255)]
    private ?string $firstname = '';

    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(length: 255)]
    private ?string $lastname = '';

    #[Groups(['user:write', 'user:read'])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ApiFilter(DateFilter::class)]
    #[Groups(['user:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'appointmentUser', targetEntity: Appointment::class)]
    private Collection $appointments;

    #[ORM\OneToMany(mappedBy: 'provider', targetEntity: Establishment::class)]
    private Collection $establishments;

    #[ORM\ManyToOne(inversedBy: 'employees')]
    private ?Establishment $establishmentEmployee = null;

    #[ORM\OneToMany(mappedBy: 'employee', targetEntity: ProvisionEmployee::class)]
    private Collection $provisionEmployees;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $kbis = null;

    #[ORM\Column(nullable: true)]
    private ?bool $active = null;

    #[ORM\OneToMany(mappedBy: 'employee', targetEntity: PlanningEmployee::class)]
    private Collection $planningEmployees;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->appointments = new ArrayCollection();
        $this->establishments = new ArrayCollection();
        $this->provisionEmployees = new ArrayCollection();
        $this->planningEmployees = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Publication $post): void
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }
    }

    public function removePost(Publication $post): void
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): void
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAuthor($this);
        }
    }

    public function removeComment(Comment $comment): void
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): void
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->addBuyer($this);
        }
    }

    public function removeProduct(Product $product): void
    {
        if ($this->products->removeElement($product)) {
            $product->removeBuyer($this);
        }
    }

    public function hasProduct(Product $object): bool
    {
        foreach ($this->products as $product) {
            if ($product->getId() === $object->getId()) {
                return true;
            }
        }

        return false;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setAppointmentUser($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getAppointmentUser() === $this) {
                $appointment->setAppointmentUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Establishment>
     */
    public function getEstablishments(): Collection
    {
        return $this->establishments;
    }

    public function addEstablishment(Establishment $establishment): static
    {
        if (!$this->establishments->contains($establishment)) {
            $this->establishments->add($establishment);
            $establishment->setProvider($this);
        }

        return $this;
    }

    public function removeEstablishment(Establishment $establishment): static
    {
        if ($this->establishments->removeElement($establishment)) {
            // set the owning side to null (unless already changed)
            if ($establishment->getProvider() === $this) {
                $establishment->setProvider(null);
            }
        }

        return $this;
    }

    public function getEstablishmentEmployee(): ?Establishment
    {
        return $this->establishmentEmployee;
    }

    public function setEstablishmentEmployee(?Establishment $establishmentEmployee): static
    {
        $this->establishmentEmployee = $establishmentEmployee;

        return $this;
    }

    /**
     * @return Collection<int, ProvisionEmployee>
     */
    public function getProvisionEmployees(): Collection
    {
        return $this->provisionEmployees;
    }

    public function addProvisionEmployee(ProvisionEmployee $provisionEmployee): static
    {
        if (!$this->provisionEmployees->contains($provisionEmployee)) {
            $this->provisionEmployees->add($provisionEmployee);
            $provisionEmployee->setEmployee($this);
        }

        return $this;
    }

    public function removeProvisionEmployee(ProvisionEmployee $provisionEmployee): static
    {
        if ($this->provisionEmployees->removeElement($provisionEmployee)) {
            // set the owning side to null (unless already changed)
            if ($provisionEmployee->getEmployee() === $this) {
                $provisionEmployee->setEmployee(null);
            }
        }

        return $this;
    }

    public function getKbis(): ?string
    {
        return $this->kbis;
    }

    public function setKbis(?string $kbis): static
    {
        $this->kbis = $kbis;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, PlanningEmployee>
     */
    public function getPlanningEmployees(): Collection
    {
        return $this->planningEmployees;
    }

    public function addPlanningEmployee(PlanningEmployee $planningEmployee): static
    {
        if (!$this->planningEmployees->contains($planningEmployee)) {
            $this->planningEmployees->add($planningEmployee);
            $planningEmployee->setEmployee($this);
        }

        return $this;
    }

    public function removePlanningEmployee(PlanningEmployee $planningEmployee): static
    {
        if ($this->planningEmployees->removeElement($planningEmployee)) {
            // set the owning side to null (unless already changed)
            if ($planningEmployee->getEmployee() === $this) {
                $planningEmployee->setEmployee(null);
            }
        }

        return $this;
    }
}
