<?php

namespace Craftworks\GenDiff\Pretty;

function render($tree, $depth = 0)
{
    $result = $tree
        ->map(function ($node) {
            return renderNode($node);
        })
        ->flatten(1)
        ->map(function ($renderedNode) use ($depth) {
            list($flag, $key, $value) = $renderedNode;
            $indent = 3 + $depth * 4;
            return sprintf("% {$indent}s %s: %s", $flag, $key, is_object($value) ? render($value, $depth + 1) : $value);
        })
        ->all();
    return "{\n" . implode("\n", $result) . "\n" . str_repeat(' ', $depth * 4) . "}";
}

function renderNode($node)
{
    switch ($node['type']) {
        case 'added':
            return [['+', $node['key'], $node['afterValue']]];
        case 'removed':
            return [['-', $node['key'], $node['beforeValue']]];
        case 'nested':
            return [['', $node['key'], $node['afterValue']]];
        case 'equal':
            return [['', $node['key'], $node['afterValue']]];
    }
    return [['+', $node['key'], $node['afterValue']],
            ['-', $node['key'], $node['beforeValue']]];
}
