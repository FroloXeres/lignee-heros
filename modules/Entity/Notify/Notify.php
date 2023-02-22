<?php

namespace LdH\Entity\Notify;

class Notify
{
    public const TYPE_CITY_CHOICE = 'cityChoice';

    protected string $type;
    protected string $log;
    protected array $arguments;

    /**
     * @param string $type
     * @param string $log
     * @param array  $arguments
     */
    public function __construct(string $type, string $log = '', array $arguments = [])
    {
        $this->type      = $type;
        $this->log       = $log;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLog(): string
    {
        return $this->log;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
