<?php
namespace Formapro\Pvm\Builder;

use Formapro\Pvm\Node;
use Formapro\Pvm\ProcessBuilder;
use Formapro\Pvm\Uuid;
use function Formapro\Values\get_value;

class NodeBuilder
{
    /**
     * @var ProcessBuilder
     */
    private $processBuilder;

    /**
     * @var Node
     */
    private $node;

    public function __construct(ProcessBuilder $processBuilder, Node $node)
    {
        $this->processBuilder = $processBuilder;

        $this->node = $node;

        if (false == get_value($this->node, 'id')) {
            $this->node->setId(Uuid::generate());
        }
    }

    public function setId(string $id): self
    {
        $this->node->setId($id);

        return $this;
    }

    public function setLabel(string $label): self
    {
        $this->node->setLabel($label);

        return $this;
    }

    public function setBehavior(string $behavior): self
    {
        $this->node->setBehavior($behavior);

        return $this;
    }

    public function setOption(string $key, $value): self
    {
        $this->node->setOption($key, $value);

        return $this;
    }

    public function end(): ProcessBuilder
    {
        return $this->processBuilder;
    }

    public function getNode(): Node
    {
        return $this->node;
    }
}
