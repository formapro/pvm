<?php
namespace Formapro\Pvm\Visual;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Formapro\Pvm\Node;
use Formapro\Pvm\Process;
use Formapro\Pvm\Token;
use Formapro\Pvm\TokenTransition;
use Formapro\Pvm\Transition;
use Graphp\GraphViz\GraphViz;
use function Makasim\Values\get_object;
use function Makasim\Values\get_value;

class VisualizeFlow
{
    public function createGraph(Process $process)
    {
        $graph = new Graph();
        $graph->setAttribute('graphviz.graph.rankdir', 'TB');
        $graph->setAttribute('graphviz.graph.ranksep', 1);
//        $graph->setAttribute('graphviz.graph.constraint', false);
//        $graph->setAttribute('graphviz.graph.splines', 'ortho');
        $graph->setAttribute('alom.graphviz', [
            'rankdir' => 'TB',
            'ranksep' => 1,
        ]);

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
            }

            if (empty($process->getOutTransitions($transition->getTo()))) {
                $this->createEndTransition($graph, $endVertex, $transition);
            }
        }

        return $graph;
    }

    /**
     * @param Graph $graph
     * @param Process $process
     * @param Token[] $tokens
     */
    public function applyTokens(Graph $graph, Process $process, array $tokens = [])
    {
        $endVertex = $this->createEndVertex($graph);

        foreach ($tokens as $token) {
            foreach ($token->getTransitions() as $tokenTransition) {
                $hasException = get_value($tokenTransition, 'exception', false);

                $transition = $tokenTransition->getTransition();
                $edge = $this->findTransitionEdge($graph, $transition);

                $alomEdgeAttributes = $edge->getAttribute('alom.graphviz', []);

                if ($edge->getAttribute('pvm.state') === TokenTransition::STATE_PASSED) {
                    continue;
                }

                $edge->setAttribute('pvm.state', $tokenTransition->getState());
                $edge->setAttribute('graphviz.color', $this->guessTransitionColor($tokenTransition));
                $alomEdgeAttributes['color'] = $this->guessTransitionColor($tokenTransition);

                if ($hasException) {
                    $edge->getVertexEnd()->setAttribute('graphviz.color', 'red');

                    $vertexEndAlomAttributes = $edge->getVertexEnd()->getAttribute('alom.graphviz', []);
                    $vertexEndAlomAttributes['color'] = 'red';

                    $edge->getVertexEnd()->setAttribute('alom.graphviz', $vertexEndAlomAttributes);
                }

                if (empty($process->getOutTransitions($transition->getTo()))) {
                    $from = $graph->getVertex($transition->getTo()->getId());
                    $endEdge = $from->getEdgesTo($endVertex)->getEdgeFirst();

                    if ($edge->getAttribute('pvm.state') === TokenTransition::STATE_PASSED) {
                        $endEdge->setAttribute('pvm.state', $tokenTransition->getState());
                        $endEdge->setAttribute('graphviz.color', $this->guessTransitionColor($tokenTransition));

                        $endEdgeAlomAttribute = $endEdge->getAttribute('alom.graphviz', []);
                        $endEdgeAlomAttribute['color'] = $this->guessTransitionColor($tokenTransition);
                        $endEdge->setAttribute('alom.graphviz', $endEdgeAlomAttribute);
                    }
                }

                $edge->setAttribute('alom.graphviz', $alomEdgeAttributes);
            }
        }
    }

    public function createImageSrc(Graph $graph)
    {
        return (new GraphViz())->createImageSrc($graph);
    }

    public function display(Graph $graph)
    {
        (new GraphViz())->display($graph);
    }

    private function createVertex(Graph $graph, Node $node)
    {
        /** @var Options $options */
        $options = get_object($node, 'visual', Options::class) ?: new Options();
        $vertex = $graph->createVertex($node->getId());
        $vertex->setAttribute('graphviz.label', $node->getLabel());
        $vertex->setAttribute('graphviz.id', $node->getId());

        if (null !== $groupId = $node->getOption('group')) {
            $vertex->setAttribute('alom.graphviz_subgroup', $groupId);
        }

        switch ($options->getType()) {
            case 'gateway':
                $shape = 'diamond';
                break;
            default:
                $shape = 'box';
        }

        $vertex->setAttribute('graphviz.shape', $shape);

        $vertex->setAttribute('alom.graphviz', [
            'label' => $node->getLabel(),
            'id' => $node->getId(),
            'shape' => $shape,
        ]);

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

        $edge->setAttribute('alom.graphviz', [
            'label' => $transition->getName(),
        ]);
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

        $edge->setAttribute('alom.graphviz', [
            'label' => $transition->getName(),
        ]);
    }

    private function createMiddleTransition(Graph $graph, Transition $transition)
    {
        $from = $graph->getVertex($transition->getFrom()->getId());
        $to = $graph->getVertex($transition->getTo()->getId());

        $edge = $from->createEdgeTo($to);
        $edge->setAttribute('pvm.transition_id', $transition->getId());
        $edge->setAttribute('graphviz.id', $transition->getId());
        $edge->setAttribute(
            'graphviz.label',
            $transition->getName()
        );

        $edge->setAttribute('alom.graphviz', [
            'id' => $transition->getId(),
            'label' => $transition->getName(),
        ]);
    }

    /**
     * @param Graph $graph
     *
     * @return Vertex
     */
    private function createStartVertex(Graph $graph)
    {
        if (false == $graph->hasVertex('__start')) {
            $vertex = $graph->createVertex('__start');
            $vertex->setAttribute('graphviz.label', 'Start');
            $vertex->setAttribute('graphviz.color', 'blue');
            $vertex->setAttribute('graphviz.shape', 'circle');

            $vertex->setAttribute('alom.graphviz', [
                'label' => 'Start',
                'color' => 'blue',
                'shape' => 'circle',
            ]);
        }

        return $graph->getVertex('__start');
    }

    /**
     * @param Graph $graph
     *
     * @return Vertex
     */
    private function createEndVertex(Graph $graph)
    {
        if (false == $graph->hasVertex('__end')) {
            $vertex = $graph->createVertex('__end');
            $vertex->setAttribute('graphviz.label', 'End');
            $vertex->setAttribute('graphviz.color', 'red');
            $vertex->setAttribute('graphviz.shape', 'circle');

            $vertex->setAttribute('alom.graphviz', [
                'label' => 'End',
                'color' => 'red',
                'shape' => 'circle',
            ]);
        }

        return $graph->getVertex('__end');
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
