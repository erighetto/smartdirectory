<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CncatCatLinear
 *
 * @ORM\Table(name="cncat_cat_linear")
 * @ORM\Entity
 */
class CncatCatLinear
{
    /**
     * @var int
     *
     * @ORM\Column(name="cid", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $cid;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $name = 'NULL';

    public function getCid(): ?int
    {
        return $this->cid;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }


}
