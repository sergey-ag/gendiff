<?php

namespace CraftWorks\GenDiff\Plain;

function render($tree, $path = '')
{
    return $tree
        ->map(function ($node) use ($path) {
            return renderNode($node, $path);
        })
        ->flatten()
        ->implode("\n");
}

function renderNode($node, $path)
{
    switch ($node['type']) {
        case 'added':
            return [["Property '{$path}{$node['key']}' was added with value: " . renderValue($node['afterValue'])]];
        case 'removed':
            return [["Property '{$path}{$node['key']}' was removed"]];
        case 'nested':
            return [render($node['afterValue'], $node['key'] . '.')];
        case 'equal':
            return [];
    }
    return [["Property '{$path}{$node['key']}' was changed. From " .
              renderValue($node['beforeValue']) . ' to ' . renderValue($node['afterValue'])]];
}

function renderValue($value)
{
    return is_object($value) ? "'complex value'" : "'" . $value . "'";
}
