<?php


namespace LdH\Entity;


class Tile {
    protected ?int     $id       = null;
    protected int      $x        = 0;
    protected int      $y        = 0;
    protected int      $howFar   = 0;
    protected bool     $flip     = false;
    protected bool     $disabled = false;
    protected ?Terrain $terrain = null;

    public function __construct(int $id, int $x, int $y, int $howFar = 0, bool $disabled = false, bool $flip = false)
    {
        $this->id       = $id;
        $this->x        = $x;
        $this->y        = $y;
        $this->howFar   = $howFar;
        $this->disabled = $disabled;
        $this->flip     = $flip;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * @param int $x
     */
    public function setX(int $x): void
    {
        $this->x = $x;
    }

    /**
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

    /**
     * @param int $y
     */
    public function setY(int $y): void
    {
        $this->y = $y;
    }

    /**
     * @return int
     */
    public function getHowFar(): int
    {
        return $this->howFar;
    }

    /**
     * @param int $howFar
     */
    public function setHowFar(int $howFar): void
    {
        $this->howFar = $howFar;
    }

    /**
     * @return bool
     */
    public function isFlip(): bool
    {
        return $this->flip;
    }

    /**
     * @param bool $flip
     */
    public function setFlip(bool $flip): void
    {
        $this->flip = $flip;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     */
    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }

    /**
     * @return Terrain|null
     */
    public function getTerrain(): ?Terrain
    {
        return $this->terrain;
    }

    /**
     * @param Terrain|null $terrain
     */
    public function setTerrain(?Terrain $terrain): void
    {
        $this->terrain = $terrain;
    }
}
