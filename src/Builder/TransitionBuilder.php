<?php
namespace Formapro\Pvm\Builder;

use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Transition;
use Formapro\Pvm\Uuid;
use function Makasim\Values\get_value;

class TransitionBuilder
{
    /**
     * @var ProcessBuilder
     */
    private $processBuilder;

    /**
     * @var Transition
     */
    private $transition;

    public function __construct(ProcessBuilder $processBuilder, Transition $transition)
    {
        $this->processBuilder = $processBuilder;
        $this->transition = $transition;

        if (false == get_value($this->transition, 'id')) {
            $this->transition->setId(Uuid::generate());
        }
    }

    public function setId(string $id): self
    {
        $this->transition->setId($id);

        return $this;
    }

    public function setName(string $name): self
    {
        $this->transition->setName($name);

        return $this;
    }

    public function setWeight(int $weight): self
    {
        $this->transition->setWeight($weight);

        return $this;
    }

    public function setAsync(bool $async): self
    {
        $this->transition->setAsync($async);

        return $this;
    }

    public function setActive(bool $active): self
    {
        $this->transition->setActive($active);

        return $this;
    }

    public function end(): ProcessBuilder
    {
        return $this->processBuilder;
    }

    public function getTransition(): Transition
    {
        return $this->transition;
    }
}
