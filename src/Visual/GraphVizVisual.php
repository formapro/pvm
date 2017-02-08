<?php
namespace Formapro\Pvm\Visual;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Formapro\Pvm\Node;
use Formapro\Pvm\Process;
use Formapro\Pvm\Transition;
use Graphp\GraphViz\GraphViz;

class GraphVizVisual
{
    /**
     * @var Vertex
     */
    private $vertext = [];

    public function createImageSrc(Process $process)
    {
        try {
            return (new GraphViz())->createImageSrc($this->createGraph($process));
        } finally {
            $this->vertext = [];
        }
    }

    public function display(Process $process)
    {
        try {
            (new GraphViz())->display($this->createGraph($process));
        } finally {
            $this->vertext = [];
        }
    }

    public function createGraph(Process $process)
    {
        $graph = new Graph();
        $graph->setAttribute('graphviz.graph.rankdir', 'TB');
//        $graph->setAttribute('graphviz.graph.constraint', false);
//        $graph->setAttribute('graphviz.graph.splines', 'ortho');

        foreach ($process->getTransitions() as $transition) {
            // from
            if (false == $transition->getFrom()) {
                $from = $this->createStartNode($graph);
            } else {
                $from = $this->createNode($graph, $transition->getFrom());
            }

            // to
            $to = $this->createNode($graph, $transition->getTo());

            // transition
            $this->createTransition($from, $to, $transition);

            //  end
            if (false == $process->getOutTransitions($transition->getTo())) {
                $this->createEndNode($graph, $to);
            }
        }

        return $graph;
    }

    private function createStartNode(Graph $graph)
    {
        if (isset($this->vertext['__start'])) {
            return $this->vertext['__start'];
        }

        $vertext = $graph->createVertex('start');
        $vertext->setAttribute('graphviz.color', 'blue');
        $vertext->setAttribute('graphviz.shape', 'circle');

        $this->vertext['__start'] = $vertext;

        return $vertext;
    }

    private function createEndNode(Graph $graph, Vertex $from)
    {
        if (false == isset($this->vertext['__end'])) {
            $vertext = $graph->createVertex('end');
            $vertext->setAttribute('graphviz.color', 'red');
            $vertext->setAttribute('graphviz.shape', 'circle');

            $this->vertext['__end'] = $vertext;
        }

        $to = $this->vertext['__end'];

        $from->createEdgeTo($to);
    }

    private function createNode(Graph $graph, Node $node)
    {
        if (isset($this->vertext[$node->getId()])) {
            return $this->vertext[$node->getId()];
        }

        /** @var Options $options */
        $options = $node->getObject('visual', Options::class) ?: new Options();
        $vertext = $graph->createVertex($node->getLabel());

        switch ($options->getType()) {
            case 'gateway':
                $shape = 'diamond';
                break;
            default:
                $shape = 'box';
        }

        $vertext->setAttribute('graphviz.shape', $shape);

        $this->vertext[$node->getId()] = $vertext;

        return $vertext;
    }

    private function createTransition(Vertex $from, Vertex $to, Transition $transition)
    {
        $edge = $from->createEdgeTo($to);

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

        $edge->setAttribute('graphviz.color', $transitionColor);
        $edge->setAttribute('graphviz.label', $transition->getState());
    }
}
