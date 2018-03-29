<?php

namespace Craftworks\GenDiff;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Collection;

function compare($firstFile, $secondFile, $format = 'pretty')
{
    $file1 = file_get_contents($firstFile);
    $file2 = file_get_contents($secondFile);
    $parse = [
        'json' => function ($json) {
            return json_decode($json);
        },
        'yml' => function ($yaml) {
            return Yaml::parse($yaml, Yaml::PARSE_OBJECT_FOR_MAP);
        }
    ];
    $inputFormat = pathinfo($firstFile, PATHINFO_EXTENSION);
    $data1 = $parse[$inputFormat]($file1);
    $data2 = $parse[$inputFormat]($file2);
    $tree = buildDiff($data1, $data2);
    return render($tree);
}

function buildDiff($data1, $data2)
{
    $arrData1 = boolToString(get_object_vars($data1));
    $arrData2 = boolToString(get_object_vars($data2));
    $keys = \Funct\Collection\union(array_keys($arrData1), array_keys($arrData2));
    $result = array_reduce($keys, function ($acc, $key) use ($arrData1, $arrData2) {
        return $acc->push(getNode($key, $arrData1, $arrData2));
    }, collect());
    return $result;
}

function boolToString($array)
{
    return array_map(function ($value) {
        if ($value === true) {
            return 'true';
        }
        if ($value === false) {
            return 'false';
        }
        return $value;
    }, $array);
}

function getNode($key, $data1, $data2)
{
    if (!key_exists($key, $data1)) {
        return ['type' => 'added', 'key' => $key,
                'afterValue' => is_object($data2[$key]) ? buildDiff($data2[$key], $data2[$key]) : $data2[$key]];
    }
    if (!key_exists($key, $data2)) {
        return ['type' => 'removed', 'key' => $key,
                'beforeValue' => is_object($data1[$key]) ? buildDiff($data1[$key], $data1[$key]) : $data1[$key]];
    }
    if (is_object($data1[$key]) && is_object($data2[$key])) {
        return ['type' => 'nested', 'key' => $key, 'afterValue' => buildDiff($data1[$key], $data2[$key])];
    }
    if ($data1[$key] === $data2[$key]) {
        return ['type' => 'equal', 'key' => $key, 'afterValue' => $data2[$key]];
    }
    return ['type' => 'changed', 'key' => $key,
            'beforeValue' => is_object($data1[$key]) ? buildDiff($data1[$key], $data1[$key]) : $data1[$key],
            'afterValue' => is_object($data2[$key]) ? buildDiff($data2[$key], $data2[$key]) : $data2[$key]];
}

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
