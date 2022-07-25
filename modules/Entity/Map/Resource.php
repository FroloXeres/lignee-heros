<?php


namespace LdH\Entity\Map;


class Resource implements \JsonSerializable
{
    protected string $code        = '';
    protected string $name        = '';
    protected string $description = '';

    /**
     * Resource constructor.
     *
     * @param string $name
     * @param string $code
     * @param string $description
     */
    public function __construct(string $name, string $code, string $description)
    {
        $this->name        = $name;
        $this->code        = $code;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'code'        => $this->getCode(),
            'name'        => $this->getName(),
            'description' => $this->getDescription()
        ];
    }
}
