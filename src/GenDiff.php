<?php

namespace Craftworks\GenDiff;

function diff($format, $firstFile, $secondFile)
{
    $file1 = file_get_contents($firstFile);
    $file2 = file_get_contents($secondFile);
    $data1 = json_decode($file1);
    $data2 = json_decode($file2);
    $tree = build($data1, $data2);
    return render($tree);
}

function build($data1, $data2)
{
    $arrData1 = boolToString(get_object_vars($data1));
    $arrData2 = boolToString(get_object_vars($data2));
    $keys = \Funct\Collection\union(array_keys($arrData1), array_keys($arrData2));
    $result = array_reduce($keys, function ($acc, $key) use ($arrData1, $arrData2) {
        $acc[] = getNode($key, $arrData1, $arrData2);
        return $acc;
    }, []);
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
        return ['type' => 'added', 'key' => $key, 'value' => $data2[$key]];
    }
    if (!key_exists($key, $data2)) {
        return ['type' => 'removed', 'key' => $key, 'value' => $data1[$key]];
    }
    if ($data1[$key] === $data2[$key]) {
        return ['type' => 'equal', 'key' => $key, 'value' => $data1[$key]];
    }
    return ['type' => 'changed', 'key' => $key, 'value' => $data2[$key], 'prevValue' => $data1[$key]];
}

function render($tree)
{
    $result = array_reduce($tree, function ($acc, $node) {
        return $acc . renderNode($node);
    }, '');
    return "{\n{$result}}";
}

function renderNode($node)
{
    switch ($node['type']) {
        case 'added':
            return "  + {$node['key']}: {$node['value']}\n";
        case 'removed':
            return "  - {$node['key']}: {$node['value']}\n";
        case 'equal':
            return "    {$node['key']}: {$node['value']}\n";
    }
    return "  + {$node['key']}: {$node['value']}\n" .
           "  - {$node['key']}: {$node['prevValue']}\n";
}
