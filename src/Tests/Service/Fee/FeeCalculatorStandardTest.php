<?php

declare(strict_types=1);

namespace Lendable\Interview\Interpolation\Tests\Service\Fee;

use Lendable\Interview\Interpolation\Service\Fee\FeeCalculatorStandard;
use Lendable\Interview\Interpolation\Model\LoanApplication;
use PHPUnit\Framework\TestCase;

class FeeCalculatorStandardTest extends TestCase
{
    /**
     * @var FeeCalculatorStandard
     */
    private $calculator;

    public function setUp()
    {
        $this->calculator = new FeeCalculatorStandard();
    }

    /**
     * @param int $term
     * @param float $loanAmount
     * @param float $expectedFee
     * @return void
     * @dataProvider getTestCalculate
     */
    public function testCalculate(int $term, float $loanAmount, float $expectedFee): void
    {
        $loanApplication = new LoanApplication($term, $loanAmount);

        $this->assertSame($expectedFee, $this->calculator->calculate($loanApplication));
    }

    /**
     * @return array
     */
    public function getTestCalculate(): array
    {
        return [
            //  #term    #loanAmount  #expectedFee
            [   12,         1000,       50  ],
            [   12,         1500,       70  ],
            [   12,         2000,       90  ],
            [   12,         3000,       90  ],
            [   12,         3525,       105  ],
            [   12,         4000,       115  ],
            // Assume as 5000 is lower we decrement
            [   12,         4600,       110  ],
            [   12,         5000,       100  ],
            [   12,         6000,       120  ],
            [   12,         7000,       140  ],
            [   12,         8000,       160  ],
            [   12,         9000,       180  ],
            [   12,         10000,       200  ],
            [   12,         11000,       220  ],
            [   12,         12000,       240  ],
            [   12,         13000,       260  ],
            [   12,         14000,       280  ],
            [   12,         15000,       300  ],
            [   12,         16000,       320  ],
            [   12,         17000,       340  ],
            [   12,         18000,       360  ],
            [   12,         19000,       380  ],
            [   12,         20000,       400  ],
            [   24,         1000,       70  ],
            [   24,         2000,       100  ],
            [   24,         3000,       120  ],
            [   24,         4000,       160  ],
            [   24,         5000,       200  ],
            [   24,         6000,       240  ],
            [   24,         7000,       280  ],
            [   24,         8000,       320  ],
            [   24,         9000,       360  ],
            [   24,         10000,       400  ],
            [   24,         11000,       440  ],
            [   24,         12000,       480  ],
            [   24,         13000,       520  ],
            [   24,         14000,       560  ],
            [   24,         15000,       600  ],
            [   24,         16000,       640  ],
            [   24,         17000,       680  ],
            [   24,         18000,       720  ],
            [   24,         19000,       760  ],
            [   24,         20000,       800  ],
        ];
    }

    /**
     * @expectedException Lendable\Interview\Interpolation\Exceptions\ExceptionLoanAmountTooLow
     * @expectedExceptionMessage The loan amount is too low
     */
    public function testLoanTooLowForCalculate()
    {
        $loanApplication = new LoanApplication(12, FeeCalculatorStandard::MINIMUM_LOAN_AMOUNT - 1);
        $this->calculator->calculate($loanApplication);
    }

    /**
     * @expectedException Lendable\Interview\Interpolation\Exceptions\ExceptionLoanAmountTooHigh
     * @expectedExceptionMessage The loan amount is too high
     */
    public function testLoanTooHighForCalculate()
    {
        $loanApplication = new LoanApplication(12, FeeCalculatorStandard::MAXIMUM_LOAN_AMOUNT + 1);
        $this->calculator->calculate($loanApplication);
    }

    /**
     * @expectedException Lendable\Interview\Interpolation\Exceptions\ExceptionInvalidLoanPeriod
     * @expectedExceptionMessage Invalid load period provided
     */
    public function testInvalidPeriodForCalculate()
    {
        $loanApplication = new LoanApplication(18, FeeCalculatorStandard::MAXIMUM_LOAN_AMOUNT - 1);
        $this->calculator->calculate($loanApplication);
    }
}
