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
     * @ORM\ManyToMany(targetEntity="App\Entity\QuestionAnswer", mappedBy="reactions")
     */
    private $questions;

    public function __construct()
    {
        $this->questionReactions = new ArrayCollection();
        $this->questions = new ArrayCollection();
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
     * @return Collection|QuestionAnswer[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(QuestionAnswer $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->addReaction($this);
        }

        return $this;
    }

    public function removeQuestion(QuestionAnswer $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            $question->removeReaction($this);
        }

        return $this;
    }
}
