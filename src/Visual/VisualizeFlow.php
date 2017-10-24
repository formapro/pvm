<?php
namespace Formapro\Pvm\Visual;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Formapro\Pvm\Node;
use Formapro\Pvm\Process;
use Formapro\Pvm\TokenTransition;
use Formapro\Pvm\Transition;
use Formapro\Pvm\Uuid;
use Graphp\GraphViz\GraphViz;
use function Makasim\Values\get_object;
use function Makasim\Values\get_value;

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

        foreach ($process->getTokens() as $token) {
            foreach ($token->getTransitions() as $tokenTransition) {
                $transition = $tokenTransition->getTransition();
                $edge = $this->findTransitionEdge($graph, $transition);

                if ($edge->getAttribute('pvm.state') === TokenTransition::STATE_PASSED) {
                    continue;
                }

                $edge->setAttribute('pvm.state', $tokenTransition->getState());
                $edge->setAttribute('graphviz.color', $this->guessTransitionColor($tokenTransition));

                if (empty($process->getOutTransitions($transition->getTo()))) {
                    $from = $graph->getVertex($transition->getTo()->getId());
                    $edge = $from->getEdgesTo($endVertex)->getEdgeFirst();

                    if ($edge->getAttribute('pvm.state') !== TokenTransition::STATE_PASSED) {
                        $edge->setAttribute('pvm.state', $tokenTransition->getState());
                        $edge->setAttribute('graphviz.color', $this->guessTransitionColor($tokenTransition));
                    }
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
        $edge->setAttribute('pvm.transition_id', $transition->getId());
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

        $edge->setAttribute('graphviz.label', $transition->getName());
    }

    private function createMiddleTransition(Graph $graph, Transition $transition)
    {
        $from = $graph->getVertex($transition->getFrom()->getId());
        $to = $graph->getVertex($transition->getTo()->getId());

        $edge = $from->createEdgeTo($to);
        $edge->setAttribute('pvm.transition_id', $transition->getId());
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

    private function guessTransitionColor(TokenTransition $transition): string
    {
        switch ($transition->getState()) {
            case TokenTransition::STATE_INTERRUPTED:
                $transitionColor = 'red';
                break;
            case TokenTransition::STATE_PASSED:
                $transitionColor = 'blue';
                break;
            case TokenTransition::STATE_WAITING:
                $transitionColor = 'orange';
                break;
            default:
                $transitionColor = 'black';
        }

        return $transitionColor;
    }

    private function findTransitionEdge(Graph $graph, Transition $transition): Directed
    {
        foreach ($graph->getEdges() as $edge) {
            /** @var Directed $edge */

            if ($edge->getAttribute('pvm.transition_id') === $transition->getId()) {
                return $edge;
            }
        }

        throw new \LogicException(sprintf('The edge for transition "%s" could not be found.', $transition->getId()));
    }
}
