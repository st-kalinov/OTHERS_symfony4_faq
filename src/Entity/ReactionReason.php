<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="App\Repository\ReactionReasonRepository")
 */
class ReactionReason
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $reason;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $reaction_category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\QuestionReaction", mappedBy="reaction", orphanRemoval=true)
     */
    private $questionReactions;

    public function __construct()
    {
        $this->questionReactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getReactionCategory(): ?string
    {
        return $this->reaction_category;
    }

    public function setReactionCategory(string $reaction_category): self
    {
        $this->reaction_category = $reaction_category;

        return $this;
    }

    /**
     * @return Collection|QuestionReaction[]
     */
    public function getQuestionReactions(): Collection
    {
        return $this->questionReactions;
    }

    public function addQuestionReaction(QuestionReaction $questionReaction): self
    {
        if (!$this->questionReactions->contains($questionReaction)) {
            $this->questionReactions[] = $questionReaction;
            $questionReaction->setReaction($this);
        }

        return $this;
    }

    public function removeQuestionReaction(QuestionReaction $questionReaction): self
    {
        if ($this->questionReactions->contains($questionReaction)) {
            $this->questionReactions->removeElement($questionReaction);
            // set the owning side to null (unless already changed)
            if ($questionReaction->getReaction() === $this) {
                $questionReaction->setReaction(null);
            }
        }

        return $this;
    }

}
