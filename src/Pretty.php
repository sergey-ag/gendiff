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
            return $value instanceof \stdClass ? [$flag, $key, renderComplexValue($value, $depth + 1)] : $renderedNode;
        })
        ->map(function ($renderedNode) use ($depth) {
            list($flag, $key, $value) = $renderedNode;
            $indent = 3 + $depth * 4;
            return sprintf(
                "% {$indent}s %s: %s",
                $flag,
                $key,
                is_object($value) ? render($value, $depth + 1) : $value
            );
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

function renderComplexValue($value, $depth)
{
    $arrValue = \Craftworks\GenDiff\boolToString(get_object_vars($value));
    $keys = array_keys($arrValue);
    $result = array_reduce($keys, function ($acc, $key) use ($arrValue, $depth) {
        $indent = 3 + $depth * 4;
        $acc[] = sprintf(
            "% {$indent}s %s: %s",
            '',
            $key,
            $arrValue[$key] instanceof \stdClass ? renderComplexValue($arrValue[$key], $depth + 1) : $arrValue[$key]
        );
        return $acc;
    }, []);
    return "{\n" . implode("\n", $result) . "\n" . str_repeat(' ', $depth * 4) . "}";
}
