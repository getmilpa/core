<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) Rodrigo Vicente - TeamX Agency — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 *
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Services;

use RuntimeException;

/**
 * Orders a set of nodes so that every dependency precedes its dependents (Kahn's algorithm).
 *
 * A generic, dependency-free graph utility: the caller supplies the node set and the directed
 * edges, and gets back the nodes in a valid topological order (dependency-first). Edges that
 * reference nodes outside the given set are ignored, so a partial graph sorts cleanly.
 */
final class TopologicalSorter
{
    /**
     * Orders the nodes so that every dependency comes out before its dependents.
     *
     * Ties keep the order the caller gave, so the same graph always sorts the
     * same way: a boot order that varies between runs turns an ordering bug into
     * one that shows up once in three runs and never while anyone is watching.
     *
     * @param list<string>                      $nodes Node names to order.
     * @param list<array{0: string, 1: string}> $edges `[dependency, dependent]` pairs — the dependency
     *                                                 must come before the dependent. Edges referencing a
     *                                                 node outside `$nodes` are ignored.
     *
     * @return list<string> The nodes in dependency-first topological order.
     *
     * @throws RuntimeException If the graph contains a cycle.
     */
    public function sort(array $nodes, array $edges): array
    {
        $graph = [];
        $indegree = [];

        foreach ($nodes as $node) {
            $graph[$node] = [];
            $indegree[$node] = 0;
        }

        foreach ($edges as $edge) {
            [$u, $v] = $edge;
            if (!in_array($u, $nodes, true) || !in_array($v, $nodes, true)) {
                continue; // Ignore edges that reach outside the given node set.
            }
            $graph[$u][] = $v;
            $indegree[$v]++;
        }

        $queue = [];
        foreach ($nodes as $node) {
            if ($indegree[$node] === 0) {
                $queue[] = $node;
            }
        }

        $result = [];
        while ($queue !== []) {
            $u = array_shift($queue);
            $result[] = $u;

            foreach ($graph[$u] as $v) {
                $indegree[$v]--;
                if ($indegree[$v] === 0) {
                    $queue[] = $v;
                }
            }
        }

        // Kahn's algorithm emits every node exactly once iff the graph is acyclic; a shorter
        // result means some nodes never reached indegree 0 — i.e. a cycle.
        if (count($result) !== count($nodes)) {
            throw new RuntimeException('Circular dependency detected.');
        }

        return $result;
    }
}
