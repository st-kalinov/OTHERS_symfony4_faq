<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuestionReactionRepository")
 */
class QuestionReaction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\QuestionAnswer", inversedBy="questionReactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $question;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ReactionReason", inversedBy="questionReactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $reaction;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?QuestionAnswer
    {
        return $this->question;
    }

    public function setQuestion(?QuestionAnswer $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getReaction(): ?ReactionReason
    {
        return $this->reaction;
    }

    public function setReaction(?ReactionReason $reaction): self
    {
        $this->reaction = $reaction;

        return $this;
    }
}
