<?php


namespace LdH\Entity\Map;


/**
 * @table="map"
 */
class Tile implements \JsonSerializable {
    /**
     * @column="tile_id"
     * @isKey
     */
    protected ?int     $id       = null;

    /**
     * @column="tile_x"
     */
    protected int      $x        = 0;

    /**
     * @column="tile_y"
     */
    protected int      $y        = 0;

    /**
     * @column="tile_far"
     */
    protected int      $howFar   = 0;

    /**
     * @column="tile_revealed"
     */
    protected bool     $flip     = false;

    /**
     * @column="tile_disabled"
     */
    protected bool     $disabled = false;

    /**
     * @column="tile_terrain"
     * @entityKey="code"
     */
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

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    public function setX(int $x): self
    {
        $this->x = $x;

        return $this;
    }

    /**
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

    public function setY(int $y): self
    {
        $this->y = $y;

        return $this;
    }

    /**
     * @return int
     */
    public function getHowFar(): int
    {
        return $this->howFar;
    }

    public function setHowFar(int $howFar): self
    {
        $this->howFar = $howFar;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFlip(): bool
    {
        return $this->flip;
    }

    public function setFlip(bool $flip): self
    {
        $this->flip = $flip;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * @return Terrain|null
     */
    public function getTerrain(): ?Terrain
    {
        return $this->terrain;
    }

    public function setTerrain(?Terrain $terrain): self
    {
        $this->terrain = $terrain;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'      => $this->id,
            'x'       => $this->x,
            'y'       => $this->y,
            'howFar'  => $this->howFar,
            'terrain' => $this->terrain ? $this->terrain->getCode() : ''
        ];
    }
}
