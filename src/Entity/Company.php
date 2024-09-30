<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['clientOrder:read', 'companies:read', 'companies:create', 'driver:read', 'driver:list'])]
    private ?int $id = null;

    #[Groups(['clientOrder:read', 'companies:read', 'companies:create', 'product:read', 'product:list', 'driver:read', 'driver:list'])]
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom de l'entreprise ne doit pas être vide.")]
    #[Assert\Length(max: 100, maxMessage: "Le nom de l'entreprise ne peut pas dépasser 100 caractères.")]
    private ?string $name = null;

    #[Groups(['companies:read', 'companies:create'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['companies:read', 'companies:create'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Groups(['companies:read', 'companies:create'])]
    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: "L'email de contact est obligatoire.")]
    #[Assert\Email(message: "L'email {{ value }} n'est pas un email valide.")]
    #[Assert\Unique(message: "L'email {{ value }} est déjà utilisé.")]
    private ?string $contactEmail = null;

    #[Groups(['companies:read', 'companies:create'])]
    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50, maxMessage: "Le numéro de téléphone ne peut pas dépasser 50 caractères.")]
    private ?string $contactPhone = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $users;

    /**
     * @var Collection<int, Driver>
     */
    #[ORM\OneToMany(targetEntity: Driver::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $drivers;

    /**
     * @var Collection<int, Vehicle>
     */
    #[ORM\OneToMany(targetEntity: Vehicle::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $vehicles;

    /**
     * @var Collection<int, Tour>
     */
    #[ORM\OneToMany(targetEntity: Tour::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $tours;

    /**
     * @var Collection<int, Delivery>
     */
    #[ORM\OneToMany(targetEntity: Delivery::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $deliveries;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $products;



    /**
     * @var Collection<int, ClientOrder>
     */
    #[ORM\OneToMany(targetEntity: ClientOrder::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $clientOrders;

    /**
     * @var Collection<int, GeocodedAddress>
     */
    #[ORM\OneToMany(targetEntity: GeocodedAddress::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $GeocodedAddresses;

    /**
     * @var Collection<int, Client>
     */
    #[ORM\OneToMany(targetEntity: Client::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $clients;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->drivers = new ArrayCollection();
        $this->vehicles = new ArrayCollection();
        $this->tours = new ArrayCollection();
        $this->deliveries = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->clientOrders = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->GeocodedAddresses = new ArrayCollection();
        $this->clients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(string $contactPhone): static
    {
        $this->contactPhone = $contactPhone;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setCompany($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCompany() === $this) {
                $user->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Driver>
     */
    public function getDrivers(): Collection
    {
        return $this->drivers;
    }

    public function addDriver(Driver $driver): static
    {
        if (!$this->drivers->contains($driver)) {
            $this->drivers->add($driver);
            $driver->setCompany($this);
        }

        return $this;
    }

    public function removeDriver(Driver $driver): static
    {
        if ($this->drivers->removeElement($driver)) {
            // set the owning side to null (unless already changed)
            if ($driver->getCompany() === $this) {
                $driver->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Vehicle>
     */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    public function addVehicle(Vehicle $vehicle): static
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles->add($vehicle);
            $vehicle->setCompany($this);
        }

        return $this;
    }

    public function removeVehicle(Vehicle $vehicle): static
    {
        if ($this->vehicles->removeElement($vehicle)) {
            // set the owning side to null (unless already changed)
            if ($vehicle->getCompany() === $this) {
                $vehicle->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tour>
     */
    public function getTours(): Collection
    {
        return $this->tours;
    }

    public function addTour(Tour $tour): static
    {
        if (!$this->tours->contains($tour)) {
            $this->tours->add($tour);
            $tour->setCompany($this);
        }

        return $this;
    }

    public function removeTour(Tour $tour): static
    {
        if ($this->tours->removeElement($tour)) {
            // set the owning side to null (unless already changed)
            if ($tour->getCompany() === $this) {
                $tour->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Delivery>
     */
    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

    public function addDelivery(Delivery $delivery): static
    {
        if (!$this->deliveries->contains($delivery)) {
            $this->deliveries->add($delivery);
            $delivery->setCompany($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): static
    {
        if ($this->deliveries->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getCompany() === $this) {
                $delivery->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCompany($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCompany() === $this) {
                $product->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClientOrder>
     */
    public function getClientOrders(): Collection
    {
        return $this->clientOrders;
    }

    public function addClientOrder(ClientOrder $clientOrder): static
    {
        if (!$this->clientOrders->contains($clientOrder)) {
            $this->clientOrders->add($clientOrder);
            $clientOrder->setCompany($this);
        }

        return $this;
    }

    public function removeClientOrder(ClientOrder $clientOrder): static
    {
        if ($this->clientOrders->removeElement($clientOrder)) {
            // set the owning side to null (unless already changed)
            if ($clientOrder->getCompany() === $this) {
                $clientOrder->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GeocodedAddress>
     */
    public function getGeocodedAddresses(): Collection
    {
        return $this->GeocodedAddresses;
    }

    public function addGeocodedAddress(GeocodedAddress $geocodedAddress): static
    {
        if (!$this->GeocodedAddresses->contains($geocodedAddress)) {
            $this->GeocodedAddresses->add($geocodedAddress);
            $geocodedAddress->setCompany($this);
        }

        return $this;
    }

    public function removeGeocodedAddress(GeocodedAddress $geocodedAddress): static
    {
        if ($this->GeocodedAddresses->removeElement($geocodedAddress)) {
            // set the owning side to null (unless already changed)
            if ($geocodedAddress->getCompany() === $this) {
                $geocodedAddress->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
            $client->setCompany($this);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getCompany() === $this) {
                $client->setCompany(null);
            }
        }

        return $this;
    }
}
