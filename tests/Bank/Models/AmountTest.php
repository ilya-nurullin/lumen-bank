<?php

namespace Bank\Models;

use App\Bank\Models\Amount;
use TestCase;

class AmountTest extends TestCase
{

    /**
     * @param $valGreater
     * @param $valLess
     *
     * @testWith ["10.00", "0.01"]
     *           ["0.02", "0.01"]
     *           ["-1.05","-1.06"]
     *           ["-10.05","-10.06"]
     */
    public function testIsLessThan($valGreater, $valLess)
    {
        $this->assertTrue(Amount::fromString($valLess)->isLessThan(Amount::fromString($valGreater)));
    }

    /**
     * @param string $val
     *
     * @testWith ["10.00"]
     * ["-1.00"]
     * ["15.34"]
     * ["0.01"]
     * ["0.10"]
     * ["0.17"]
     * ["0.99"]
     * ["109999.00"]
     * ["109999.10"]
     * ["109999.01"]
     * ["-109999.00"]
     * ["-109999.10"]
     * ["-109999.01"]
     * ["-0.01"]
     * ["-0.21"]
     * ["-3.21"]
     * ["-34.21"]
     * ["-346.21"]
     */
    public function testGetDecimal(string $val)
    {
        $this->assertEquals($val, Amount::fromString($val)->getDecimal());
    }


    /**
     * @param string $val
     * @param string $add
     * @param string $expected
     *
     * @testWith ["1.00", "0.01", "1.01"]
     *           ["1.00", "0.13", "1.13"]
     *           ["104.00", "0.13", "104.13"]
     */
    public function testAdd(string $val, string $add, string $expected)
    {
        $this->assertEquals($expected, Amount::fromString($val)->add(Amount::fromString($add))->getDecimal());
    }


    /**
     * @param string $val
     * @param string $sub
     * @param string $expected
     *
     * @testWith ["1.00", "0.01", "0.99"]
     *           ["1.00", "0.13", "0.87"]
     *           ["104.00", "0.13", "103.87"]
     */
    public function testSub(string $val, string $sub, string $expected)
    {
        $this->assertEquals($expected, Amount::fromString($val)->sub(Amount::fromString($sub))->getDecimal());
    }
}
