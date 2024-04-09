<?php

declare(strict_types=1);

namespace App\Support\Pathfinding;

use App\Exceptions\NoPathException;

/**
 * taken from https://github.com/diolan12/php-dijkstra.
 */
class Dijkstra
{
    private $prefix = '#';

    private $vertices = [];

    public function __construct(array $graph = [])
    {
        $prefixedVertices = [];
        foreach ($graph as $k => $edges) {
            $prefixedName = $this->setPrefix($k);
            $prefixedVertices[$prefixedName] = [];
            foreach ($edges as $edgeKey => $edgeValue) {
                $prefixedVertices[$prefixedName][$this->setPrefix($edgeKey)] = $edgeValue;
            }
        }
        $this->vertices = $prefixedVertices;
    }

    /**
     * Return Dijkstra's instance.
     */
    public static function instance(array $graph = []): static
    {
        return new static($graph);
    }

    /**
     * Add a vertex to the graph with its neighboring edges.
     *
     * @param string $name the name of the vertex
     * @param array $edges an associative array representing the neighboring vertices and their edge weights
     */
    public function addVertex(string $name, array $edges): static
    {
        $prefixedName = [];
        foreach ($edges as $key => $value) {
            $prefixedName[$this->setPrefix($key)] = $value;
        }
        $this->vertices[$this->setPrefix($name)] = $prefixedName;

        return $this;
    }

    /**
     * Add an edge between two vertices with a given weight.
     *
     * @param string $from the source vertex
     * @param string $to the destination vertex
     * @param int $weight the weight or cost of the edge
     */
    public function addEdge(string $from, string $to, int $weight, bool $reversible = false): static
    {
        $from = $this->setPrefix($from);
        $to = $this->setPrefix($to);

        $this->vertices[$from][$to] = $weight;

        if ($reversible) {
            $this->vertices[$to][$from] = $weight;
        }

        return $this;
    }

    /**
     * Remove an edge between two vertices with a given weight.
     *
     * @param string $from the source vertex
     * @param string $to the destination vertex
     */
    public function removeEdge(string $from, string $to, bool $reversible = false): static
    {
        $from = $this->setPrefix($from);
        $to = $this->setPrefix($to);

        unset($this->vertices[$from][$to]);

        if ($reversible) {
            unset($this->vertices[$to][$from]);
        }

        return $this;
    }

    public function getVertices(): array
    {
        return $this->vertices;
    }

    /**
     * Dijkstra's shortest path algorithm
     * [Wikipedia](https://en.wikipedia.org/wiki/Dijkstra%27s_algorithm).
     *
     * @see https://en.wikipedia.org/wiki/Dijkstra%27s_algorithm
     *
     * @throws NoPathException
     */
    public function findShortestPath(string $start, string $end): array
    {
        $start = $this->setPrefix($start);
        $end = $this->setPrefix($end);
        $distances = [];
        $visited = [];
        $previous = [];

        foreach (array_keys($this->vertices) as $vertex) {
            $distances[$vertex] = INF;
            $visited[$vertex] = false;
            $previous[$vertex] = null;
        }

        $distances[$start] = 0;

        if (!isset($visited[$end])) {
            throw new NoPathException("Route from {$this->unPrefix($end)} to {$this->unPrefix($start)} not found");
        }

        while (!$visited[$end]) {
            $current = null;
            $minDist = INF;

            foreach ($distances as $vertex => $dist) {
                if (array_key_exists($vertex, $visited)) {
                    if (!$visited[$vertex] && $dist <= $minDist) {
                        $minDist = $dist;
                        $current = $vertex;
                    }
                } else {
                    throw new NoPathException('Edge "' . $this->unPrefix($vertex) . '" not found');
                }
            }

            foreach ($this->vertices[$current] as $neighbor => $cost) {
                $alt = $distances[$current] + $cost;

                if ($alt < $distances[$neighbor]) {
                    $distances[$neighbor] = $alt;
                    $previous[$neighbor] = $current;
                }
            }

            $visited[$current] = true;
        }

        $path = [];
        $current = $end;

        while ($current !== $start) {
            if (!$current) {
                throw new NoPathException("Route from {$this->unPrefix($end)} to {$this->unPrefix($start)} not found");
            }
            array_unshift($path, $current);
            $current = $previous[$current];
        }

        array_unshift($path, $start);

        foreach ($path as $k => $p) {
            $path[$k] = $this->unPrefix($p);
        }

        return $path;
    }

    private function setPrefix(string $value): string
    {
        return $this->prefix . $value;
    }

    private function unPrefix(string $value): string
    {
        return substr($value, 1);
    }
}
