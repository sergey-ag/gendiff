<?php

namespace Craftworks\GenDiff\Tests;

class GenDiffTest extends \PHPUnit\Framework\TestCase
{
    public function testDiff()
    {
        $expected1 = file_get_contents('tests/fixtures/expected1.txt');
        $expected2 = file_get_contents('tests/fixtures/expected2.txt');
        $this->assertEquals(\Craftworks\GenDiff\diff(
            'pretty',
            'tests/fixtures/before1.json',
            'tests/fixtures/after1.json'
        ), $expected1);
        $this->assertEquals(\Craftworks\GenDiff\diff(
            'pretty',
            'tests/fixtures/before2.json',
            'tests/fixtures/after2.json'
        ), $expected2);
    }
}
