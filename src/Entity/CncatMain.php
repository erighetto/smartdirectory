<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CncatMain
 *
 * @ORM\Table(name="cncat_main")
 * @ORM\Entity(repositoryClass="App\Repository\CncatMainRepository")
 */
class CncatMain
{
    /**
     * @var int
     *
     * @ORM\Column(name="lid", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $lid;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $title = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $description = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="url", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $url = 'NULL';

    /**
     * @var int|null
     *
     * @ORM\Column(name="cat1", type="integer", nullable=true, options={"default"="NULL"})
     */
    private $cat1 = 'NULL';

    /**
     * @var int|null
     *
     * @ORM\Column(name="gin", type="integer", nullable=true, options={"default"="NULL"})
     */
    private $gin = 'NULL';

    /**
     * @var int|null
     *
     * @ORM\Column(name="gout", type="integer", nullable=true, options={"default"="NULL"})
     */
    private $gout = 'NULL';

    /**
     * @var int|null
     *
     * @ORM\Column(name="moder_vote", type="integer", nullable=true, options={"default"="NULL"})
     */
    private $moderVote = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $email = 'NULL';

    /**
     * @var int|null
     *
     * @ORM\Column(name="type", type="integer", nullable=true, options={"default"="NULL"})
     */
    private $type = 'NULL';

    /**
     * @var int|null
     *
     * @ORM\Column(name="broken", type="integer", nullable=true)
     */
    private $broken = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="insert_date", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $insertDate = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="resfield1", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $resfield1 = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="resfield2", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $resfield2 = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="resfield3", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $resfield3 = 'NULL';

    public function getLid(): ?int
    {
        return $this->lid;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getCat1(): ?int
    {
        return $this->cat1;
    }

    public function setCat1(?int $cat1): self
    {
        $this->cat1 = $cat1;

        return $this;
    }

    public function getGin(): ?int
    {
        return $this->gin;
    }

    public function setGin(?int $gin): self
    {
        $this->gin = $gin;

        return $this;
    }

    public function getGout(): ?int
    {
        return $this->gout;
    }

    public function setGout(?int $gout): self
    {
        $this->gout = $gout;

        return $this;
    }

    public function getModerVote(): ?int
    {
        return $this->moderVote;
    }

    public function setModerVote(?int $moderVote): self
    {
        $this->moderVote = $moderVote;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getBroken(): ?int
    {
        return $this->broken;
    }

    public function setBroken(?int $broken): self
    {
        $this->broken = $broken;

        return $this;
    }

    public function getInsertDate(): ?\DateTimeInterface
    {
        return $this->insertDate;
    }

    public function setInsertDate(?\DateTimeInterface $insertDate): self
    {
        $this->insertDate = $insertDate;

        return $this;
    }

    public function getResfield1(): ?string
    {
        return $this->resfield1;
    }

    public function setResfield1(?string $resfield1): self
    {
        $this->resfield1 = $resfield1;

        return $this;
    }

    public function getResfield2(): ?string
    {
        return $this->resfield2;
    }

    public function setResfield2(?string $resfield2): self
    {
        $this->resfield2 = $resfield2;

        return $this;
    }

    public function getResfield3(): ?string
    {
        return $this->resfield3;
    }

    public function setResfield3(?string $resfield3): self
    {
        $this->resfield3 = $resfield3;

        return $this;
    }


}
