<?php

declare(strict_types=1);

namespace Lendable\Interview\Interpolation\Service\Fee;

use Lendable\Interview\Interpolation\Model\LoanApplication;
use Lendable\Interview\Interpolation\Exceptions\{ExceptionLoanAmountTooLow, ExceptionLoanAmountTooHigh, ExceptionInvalidLoanPeriod};


class FeeCalculatorStandard implements FeeCalculatorInterface
{
    const MINIMUM_LOAN_AMOUNT = 1000;
    const MAXIMUM_LOAN_AMOUNT = 20000;
    const TWELVE_MONTH_THRESHOLDS = [
        '1000' => 50,
        '2000' => 90,
        '3000' => 90,
        '4000' => 115,
        '5000' => 100,
        '6000' => 120,
        '7000' => 140,
        '8000' => 160,
        '9000' => 180,
        '10000' => 200,
        '11000' => 220,
        '12000' => 240,
        '13000' => 260,
        '14000' => 280,
        '15000' => 300,
        '16000' => 320,
        '17000' => 340,
        '18000' => 360,
        '19000' => 380,
        '20000' => 400,
    ];
    const TWENTY_FOUR_MONTH_THRESHOLD = [
        '1000'  => 70,
        '2000'  => 100,
        '3000'  => 120,
        '4000'  => 160,
        '5000'  => 200,
        '6000'  => 240,
        '7000'  => 280,
        '8000'  => 320,
        '9000'  => 360,
        '10000'  => 400,
        '11000'  => 440,
        '12000'  => 480,
        '13000'  => 520,
        '14000'  => 560,
        '15000'  => 600,
        '16000'  => 640,
        '17000'  => 680,
        '18000'  => 720,
        '19000'  => 760,
        '20000'  => 800,
    ];

    /**
     * @param LoanApplication $application
     * @throws \Exception
     * @return float
     */
    public function calculate(LoanApplication $application): float
    {
        if ($application->getAmount() < self::MINIMUM_LOAN_AMOUNT) {
            throw new ExceptionLoanAmountTooLow('The loan amount is too low');
        } else if ($application->getAmount() > self::MAXIMUM_LOAN_AMOUNT) {
            throw new ExceptionLoanAmountTooHigh('The loan amount is too high');
        }

        $fee = 0;
        $threshold = $this->getThresholdForPeriod($application->getTerm());

        // Amount is on the threshold
        if (true === array_key_exists((string)$application->getAmount(), $threshold)) {
            $fee = $threshold[$application->getAmount()];
        } else {
            // Get the upper and lower bounds
            list($lower, $upper) = $this->getLowerAndUpperThreshold($application);

            $lowerFee = $threshold[(string)$lower];
            $upperFee = $threshold[(string)$upper];

            // Same fee for upper and lower so allow
            if ($lowerFee === $upperFee) {
                $fee = $lowerFee;
            } else {
                // Get per pound fee
                $perPoundFee = ($upperFee - $lowerFee) / ($upper - $lower);
                $initialFee = (($perPoundFee*($application->getAmount()-$lower)))+$lowerFee;
                $total = $application->getAmount() + $initialFee;

                // Already divisible by 5
                if (fmod($total, 5) === 0.00) {
                    $fee = $initialFee;
                } else {
                    $validTotal = $this->getNearestValidTotal(
                        ($lower + $lowerFee),
                        ($upper + $upperFee),
                        $total
                    );

                    $fee = $validTotal - $application->getAmount();
                }
            }
        }

        return round($fee, 2,PHP_ROUND_HALF_UP);
    }

    /**
     * @param int $period
     * @throws ExceptionInvalidLoanPeriod
     * @return array
     */
    private function getThresholdForPeriod(int $period): array
    {
        if (false === in_array($period, [12, 24])) {
            throw new ExceptionInvalidLoanPeriod('Invalid load period provided');
        }

        if ($period === 24) {
            return self::TWENTY_FOUR_MONTH_THRESHOLD;
        }

        return self::TWELVE_MONTH_THRESHOLDS;
    }

    /**
     * @param LoanApplication $application
     * @return array
     */
    private function getLowerAndUpperThreshold(LoanApplication $application): array
    {
        return [
            round(($application->getAmount()-500), -3),
            round(($application->getAmount()+500), -3)
        ];
    }

    /**
     * @param float $totalLower
     * @param float $totalUpper
     * @param float $customTotal
     * @throws \Exception
     * @return float
     */
    private function getNearestValidTotal(float $totalLower, float $totalUpper, float $customTotal): float
    {
        foreach (range($totalLower, $totalUpper) as $total) {
            if ($total % 5 === 0) {
                if (($total-5) < $customTotal && $customTotal < $total) {
                    return $total;
                }
            }
        }

        // Should never get here but throw a generic exception just in case
        throw new \Exception('Unable to assign a valid nearest total');
    }

}
