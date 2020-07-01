<?php

namespace App\Bank\Models;

class Amount
{
    /** @var integer $val */
    private $val;

    private function __construct(int $integer)
    {
        $this->val = $integer;
    }

    public static function fromString(string $val): Amount
    {
        $integer = intval(str_replace('.', '', $val));

        return new Amount($integer);
    }

    public function getDecimal(): string
    {
        $val = abs($this->val);

        // get the last 2 digits
        $hundredth = '0' . $val % 100;
        $hundredth = substr($hundredth, -2);

        $integer = intdiv($val, 100);

        $sign = ($this->val < 0) ? '-' : '';

        return "$sign$integer.$hundredth";
    }

    public function __toString()
    {
        return $this->getDecimal();
    }

    public function isLessThan(Amount $that)
    {
        return $this->val < $that->val;
    }

    public function isPositive(): bool
    {
        return $this->val >= 0;
    }

    public function add(Amount $amount): Amount
    {
        return new Amount($this->val + $amount->val);
    }

    public function sub(Amount $amount): Amount
    {
        return new Amount($this->val - $amount->val);
    }
}
