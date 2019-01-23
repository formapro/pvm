<?php
namespace Formapro\Pvm\Builder;

use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Transition;
use Formapro\Pvm\Uuid;
use function Makasim\Values\get_value;
use function Makasim\Values\set_object;

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
        $oldId = get_value($this->transition, 'id');

        if (null !== $oldId) {
            set_object($this->processBuilder->getProcess(), 'transitions.'.$oldId, null);
        }

        $this->transition->setId($id);
        set_object($this->processBuilder->getProcess(), 'transitions.'.$id, $this->transition);

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
