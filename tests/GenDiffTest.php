<?php

namespace Craftworks\GenDiff\Tests;

class GenDiffTest extends \PHPUnit\Framework\TestCase
{
    public function testDiff()
    {
        $expected1 = file_get_contents('tests/fixtures/expected1.txt');
        $expected2 = file_get_contents('tests/fixtures/expected2.txt');
        $expected3 = file_get_contents('tests/fixtures/expected3.txt');
        $expected4 = file_get_contents('tests/fixtures/expected4.txt');
        $this->assertEquals(\Craftworks\GenDiff\compare(
            'tests/fixtures/before1.json',
            'tests/fixtures/after1.json'
        ), $expected1);
        $this->assertEquals(\Craftworks\GenDiff\compare(
            'tests/fixtures/before2.json',
            'tests/fixtures/after2.json'
        ), $expected2);
        $this->assertEquals(\Craftworks\GenDiff\compare(
            'tests/fixtures/before2.yml',
            'tests/fixtures/after2.yml'
        ), $expected2);
        $this->assertEquals(\Craftworks\GenDiff\compare(
            'tests/fixtures/before3.json',
            'tests/fixtures/after3.json'
        ), $expected3);
        $this->assertEquals(\Craftworks\GenDiff\compare(
            'tests/fixtures/before3.yml',
            'tests/fixtures/after3.yml'
        ), $expected3);
        $this->assertEquals(\Craftworks\GenDiff\compare(
            'tests/fixtures/before3.json',
            'tests/fixtures/after3.json',
            'plain'
        ), $expected4);
    }
}
