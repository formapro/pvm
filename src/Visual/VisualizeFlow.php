<?php
namespace Formapro\Pvm\Visual;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Formapro\Pvm\Node;
use Formapro\Pvm\Process;
use Formapro\Pvm\Transition;
use Formapro\Pvm\Uuid;
use Graphp\GraphViz\GraphViz;
use function Makasim\Values\get_object;

class VisualizeFlow
{
    public function createImageSrc(Process $process)
    {
        return (new GraphViz())->createImageSrc($this->createGraph($process));
    }

    public function display(Process $process)
    {
        (new GraphViz())->display($this->createGraph($process));
    }

    public function createGraph(Process $process)
    {
        $graph = new Graph();
        $graph->setAttribute('graphviz.graph.rankdir', 'TB');
        $graph->setAttribute('graphviz.graph.ranksep', 1);
//        $graph->setAttribute('graphviz.graph.constraint', false);
//        $graph->setAttribute('graphviz.graph.splines', 'ortho');

        $startVertex = $this->createStartVertex($graph);
        $endVertex = $this->createEndVertex($graph);

        foreach ($process->getNodes() as $node) {
            $this->createVertex($graph, $node);
        }
        
        foreach ($process->getTransitions() as $transition) {
            if (false == $transition->getFrom() && $transition->getTo()) {
                $this->createStartTransition($graph, $startVertex, $transition);
            }

            if ($transition->getFrom() && $transition->getTo()) {
                $this->createMiddleTransition($graph, $transition);

                if (empty($process->getOutTransitions($transition->getTo()))) {
                    $this->createEndTransition($graph, $endVertex, $transition);
                }
            }
        }

        return $graph;
    }

    private function createVertex(Graph $graph, Node $node)
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

        $vertex->setAttribute('graphviz.shape', $shape);

        return $vertex;
    }

    private function createStartTransition(Graph $graph, Vertex $from, Transition $transition)
    {
        $to = $graph->getVertex($transition->getTo()->getId());

        $edge = $from->createEdgeTo($to);

        $edge->setAttribute('graphviz.color', $this->guessTransitionColor($transition));
        $edge->setAttribute(
            'graphviz.label',
            $transition->getName()
        );
    }

    private function createEndTransition(Graph $graph, Vertex $to, Transition $transition)
    {
        $from = $graph->getVertex($transition->getTo()->getId());

        if ($from->hasEdgeTo($to)) {
            $edge = $from->getEdgesTo($to)->getEdgeFirst();
        } else {
            $edge = $from->createEdgeTo($to);
        }

        if (false == $edge->getAttribute('pvm.passed')) {
            switch ($transition->getState()) {
                case Transition::STATE_PASSED:
                    $transitionColor = 'blue';
                    $edge->setAttribute('pvm.passed', true);

                    break;
                default:
                    $transitionColor = 'black';
            }

            $edge->setAttribute('graphviz.color', $transitionColor);
        }

        $edge->setAttribute('graphviz.label', $transition->getName());
    }

    private function createMiddleTransition(Graph $graph, Transition $transition)
    {
        $from = $graph->getVertex($transition->getFrom()->getId());
        $to = $graph->getVertex($transition->getTo()->getId());

        $edge = $from->createEdgeTo($to);

        $edge->setAttribute('graphviz.color', $this->guessTransitionColor($transition));
        $edge->setAttribute(
            'graphviz.label',
            $transition->getName()
        );
    }

    /**
     * @param Graph $graph
     *
     * @return Vertex
     */
    private function createStartVertex(Graph $graph)
    {
        $vertex = $graph->createVertex(Uuid::generate());
        $vertex->setAttribute('graphviz.label', 'Start');
        $vertex->setAttribute('graphviz.color', 'blue');
        $vertex->setAttribute('graphviz.shape', 'circle');

        return $vertex;
    }

    /**
     * @param Graph $graph
     *
     * @return Vertex
     */
    private function createEndVertex(Graph $graph)
    {
        $vertex = $graph->createVertex(Uuid::generate());
        $vertex->setAttribute('graphviz.label', 'End');
        $vertex->setAttribute('graphviz.color', 'red');
        $vertex->setAttribute('graphviz.shape', 'circle');

        return $vertex;
    }

    private function guessTransitionColor(Transition $transition)
    {
        switch ($transition->getState()) {
            case Transition::STATE_INTERRUPTED:
                $transitionColor = 'red';
                break;
            case Transition::STATE_PASSED:
                $transitionColor = 'blue';
                break;
            case Transition::STATE_WAITING:
                $transitionColor = 'orange';
                break;
            default:
                $transitionColor = 'black';
        }

        return $transitionColor;
    }

}
