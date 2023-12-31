<?php // $ bin/console make:entity Product

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource]
class Product
{
    public static function loadValidatorMetadata(ClassMetadata $metadata): void // ValidatorInterface
    {
        $metadata->addPropertyConstraint('name', new Constraints\NotBlank([
            'message' => 'Field "name" is blank.',
            //'payload' => ['severity' => 'warning'],
        ]));
        $metadata->addPropertyConstraint('name', new Constraints\Regex([
            'pattern' => '/^[^K]/',
            'match' => false,
            'message' => 'Field "name" starts with letter "K".',
        ]));
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $price = null; // cents

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductCategory $category = null;

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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?ProductCategory
    {
        return $this->category;
    }

    public function setCategory(?ProductCategory $category): static
    {
        $this->category = $category;

        return $this;
    }
}
