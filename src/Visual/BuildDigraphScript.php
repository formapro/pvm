<?php
namespace Formapro\Pvm\Visual;

use Alom\Graphviz\Digraph;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex as FhacultyVertex;
use Fhaculty\Graph\Edge\Base as FhacultyEdge;
use Fhaculty\Graph\Edge\Directed as FhacultyDirectedEdge;

class BuildDigraphScript
{
    public function build(Graph $graph): string
    {
        if (false == class_exists(Digraph::class)) {
            throw new \LogicException(sprintf('The "%s" class could not be found. Make sure you\'ve run "composer require alom/graphviz".', Digraph::class));
        }

        $digraph = new Digraph('G');
        foreach ($graph->getAttribute('alom.graphviz', []) as $attrName => $attrValue) {
            $digraph->set($attrName, $attrValue);
        }

        $groups = [];
        $noGroup = [];
        $groupIdsMap = [];
        $groupIndex = 0;
        foreach ($graph->getVertices() as $vertex) {
            /** @var FhacultyVertex $vertex */

            $groupId = $vertex->getAttribute('alom.graphviz_subgroup');
            if ($groupId) {
                if (false == array_key_exists($groupId, $groups)) {
                    $groupIdsMap[$groupId] = $groupIndex;
                    $groupIndex++;

                    $groups[$groupId] = [];
                }

                $groups[$groupId][] = $vertex;
            } else {
                $noGroup[] = $vertex;
            }
        }

        foreach ($noGroup as $vertex) {
            /** @var FhacultyVertex $vertex */

            $digraph->node($vertex->getId(), $vertex->getAttribute('alom.graphviz', []));
        }

        foreach ($groups as $groupId => $vertexes) {
            $subdigraph = $digraph->subgraph('cluster_'.$groupIdsMap[$groupId]);
            $subdigraph->set('label', str_replace(' ', '\n', $groupId));
            $subdigraph->set('style', 'filled');
            $subdigraph->set('color', 'lightgrey');

            foreach ($vertexes as $vertex) {
                /** @var FhacultyVertex $vertex */

                $subdigraph->node($vertex->getId(), $vertex->getAttribute('alom.graphviz', []));
            }
        }

        foreach ($graph->getEdges() as $edge) {
            /** @var FhacultyEdge $edge */

            if ($edge instanceof FhacultyDirectedEdge) {
                $digraph->edge(
                    [$edge->getVertexStart()->getId(), $edge->getVertexEnd()->getId()],
                    $edge->getAttribute('alom.graphviz', [])
                );
            } else {
                throw new \LogicException('Unsupported edge give.');
            }

        }
//dump($digraph->render());die;
        return $digraph->render();
    }
}
