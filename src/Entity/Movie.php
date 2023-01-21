<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'movie', targetEntity: Vote::class)]
    private Collection $vote;

    #[ORM\ManyToOne(inversedBy: 'movie')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    public function __construct()
    {
        $this->vote = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVote(): Collection
    {
        return $this->vote;
    }

    public function addVote(Vote $vote): self
    {
        if (!$this->vote->contains($vote)) {
            $this->vote->add($vote);
            $vote->setMovie($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        if ($this->vote->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getMovie() === $this) {
                $vote->setMovie(null);
            }
        }

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'user_first_name' => $this->getUser()->getFirstName(),
            'user_last_name' => $this->getUser()->getLastName(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'votes' => $this->getVote(),
        ];
    }

}
