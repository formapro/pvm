<?php
namespace Formapro\Pvm\Visual;

use Fhaculty\Graph\Graph;
use Formapro\Pvm\Node;
use Formapro\Pvm\Process;
use Formapro\Pvm\Transition;
use Graphp\GraphViz\GraphViz;
use function Formapro\Values\get_object;

class VisualizeStateMachine
{
    public function createImageSrc(Process $process, $currentState)
    {
        return (new GraphViz())->createImageSrc($this->createGraph($process, $currentState));
    }

    public function display(Process $process, $currentState)
    {
        (new GraphViz())->display($this->createGraph($process, $currentState));
    }

    public function createGraph(Process $process, $currentState)
    {
        $graph = new Graph();
        $graph->setAttribute('graphviz.graph.rankdir', 'TB');
        $graph->setAttribute('graphviz.graph.ranksep', 1);
//        $graph->setAttribute('graphviz.graph.constraint', false);
//        $graph->setAttribute('graphviz.graph.splines', 'ortho');

        foreach ($process->getNodes() as $node) {
            $this->createVertex($graph, $node, $currentState);
        }
        
        foreach ($process->getTransitions() as $transition) {
            if ($transition->getFrom() && $transition->getTo()) {
                $this->createMiddleTransition($graph, $transition);
            }
        }

        return $graph;
    }

    private function createVertex(Graph $graph, Node $node, $currentState)
    {
        /** @var Options $options */
        $options = get_object($node, 'visual', Options::class) ?: new Options();
        $vertex = $graph->createVertex($node->getId());
        $vertex->setAttribute('graphviz.label', $node->getLabel());

        switch ($options->getType()) {
            case 'gateway':
                $shape = 'diamond';
                break;
            default:
                $shape = 'box';
        }

        if ($node->getLabel() === $currentState) {
            if ($node->getProcess()->getOutTransitions($node)) {
                $vertex->setAttribute('graphviz.color', 'blue');
            } else {
                $vertex->setAttribute('graphviz.color', 'red');
            }
        }

        $vertex->setAttribute('graphviz.shape', $shape);

        return $vertex;
    }

    private function createMiddleTransition(Graph $graph, Transition $transition)
    {
        $from = $graph->getVertex($transition->getFrom()->getId());
        $to = $graph->getVertex($transition->getTo()->getId());

        $edge = $from->createEdgeTo($to);

        $edge->setAttribute('graphviz.color', 'black');
        $edge->setAttribute(
            'graphviz.label',
            $transition->getName()
        );
    }
}
