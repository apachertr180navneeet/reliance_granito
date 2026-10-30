<?php

namespace App\Services;

class InvoiceCalculationService
{
    /**
     * Calculate line item amount: quantity * rate rounded to 2 decimal places.
     */
    public function calculateLineAmount(float|int|string $quantity, float|int|string $rate): float
    {
        $q = (float) $quantity;
        $r = (float) $rate;

        return round($q * $r, 2);
    }

    /**
     * Calculate all invoice totals, currency conversion, and IGST/LUT values.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public function calculateTotals(
        array $items,
        string $format = 'LUT',
        string $currency = 'USD',
        ?float $exchangeRate = null,
        float $igstRate = 18.00
    ): array {
        $totalQuantity = 0.0;
        $totalAmount = 0.0;

        foreach ($items as $item) {
            $qty = isset($item['quantity']) ? (float) $item['quantity'] : 0.0;
            $rate = isset($item['rate']) ? (float) $item['rate'] : 0.0;
            $lineAmount = isset($item['amount']) && (float) $item['amount'] > 0
                ? (float) $item['amount']
                : $this->calculateLineAmount($qty, $rate);

            $totalQuantity += $qty;
            $totalAmount += $lineAmount;
        }

        $totalAmount = round($totalAmount, 2);
        $roundedAmount = round($totalAmount);

        $isIgst = strtoupper($format) === 'IGST';
        $curr = strtoupper($currency);

        $taxableValueInr = 0.0;
        $igstAmountInr = 0.0;

        if ($curr === 'USD') {
            // Direction: 1 USD = exchangeRate INR
            $rate = ($exchangeRate && $exchangeRate > 0) ? $exchangeRate : 94.20;
            $taxableValueInr = round($totalAmount * $rate, 2);

            if ($isIgst) {
                $igstAmountInr = round($taxableValueInr * ($igstRate / 100), 2);
            }
        } else {
            // Currency is INR
            $taxableValueInr = $totalAmount;
            if ($isIgst) {
                $igstAmountInr = round($taxableValueInr * ($igstRate / 100), 2);
            }
        }

        $amountInWords = $this->convertNumberToWords($curr === 'INR' ? $roundedAmount : $totalAmount, $curr);

        return [
            'total_quantity' => round($totalQuantity, 3),
            'total_amount' => $totalAmount,
            'rounded_amount' => $roundedAmount,
            'currency' => $curr,
            'exchange_rate' => $exchangeRate,
            'igst_rate' => $isIgst ? $igstRate : 0.0,
            'taxable_value_inr' => $taxableValueInr,
            'igst_amount_inr' => $isIgst ? $igstAmountInr : 0.0,
            'amount_in_words' => $amountInWords,
        ];
    }

    /**
     * Convert an amount into formal words.
     */
    public function convertNumberToWords(float|int $amount, string $currency = 'INR'): string
    {
        $currency = strtoupper($currency);

        if ($amount <= 0) {
            return '';
        }

        if ($currency === 'USD') {
            return $this->convertUsdToWords($amount);
        }

        return $this->convertInrToWords($amount);
    }

    /**
     * Convert INR amount to Indian numbering wording (Crore, Lakh, Thousand, Hundred, Rupees).
     */
    public function convertInrToWords(float|int $amount): string
    {
        $num = (int) round($amount);
        if ($num <= 0) {
            return '';
        }

        $words = $this->inrWordsHelper($num);

        return trim($words).' RUPEES ONLY';
    }

    /**
     * Convert USD amount to words including cents.
     */
    public function convertUsdToWords(float|int $amount): string
    {
        $dollars = (int) floor($amount);
        $cents = (int) round(($amount - $dollars) * 100);

        if ($dollars === 0 && $cents === 0) {
            return '';
        }

        $dollarsStr = $dollars > 0 ? $this->westernWordsHelper($dollars).' US DOLLARS' : '';
        $centsStr = $cents > 0 ? $this->westernWordsHelper($cents).' CENTS' : '';

        if ($dollarsStr && $centsStr) {
            return $dollarsStr.' AND '.$centsStr.' ONLY';
        }

        if ($dollarsStr) {
            return $dollarsStr.' ONLY';
        }

        return $centsStr.' ONLY';
    }

    private function inrWordsHelper(int $n): string
    {
        $ones = [
            0 => '', 1 => 'ONE', 2 => 'TWO', 3 => 'THREE', 4 => 'FOUR', 5 => 'FIVE',
            6 => 'SIX', 7 => 'SEVEN', 8 => 'EIGHT', 9 => 'NINE', 10 => 'TEN',
            11 => 'ELEVEN', 12 => 'TWELVE', 13 => 'THIRTEEN', 14 => 'FOURTEEN', 15 => 'FIFTEEN',
            16 => 'SIXTEEN', 17 => 'SEVENTEEN', 18 => 'EIGHTEEN', 19 => 'NINETEEN',
        ];

        $tens = [
            2 => 'TWENTY', 3 => 'THIRTY', 4 => 'FORTY', 5 => 'FIFTY',
            6 => 'SIXTY', 7 => 'SEVENTY', 8 => 'EIGHTY', 9 => 'NINETY',
        ];

        if ($n < 20) {
            return $ones[$n];
        }

        if ($n < 100) {
            $rem = $n % 10;

            return $tens[(int) floor($n / 10)].($rem ? ' '.$ones[$rem] : '');
        }

        if ($n < 1000) {
            $rem = $n % 100;

            return $ones[(int) floor($n / 100)].' HUNDRED'.($rem ? ' & '.$this->inrWordsHelper($rem) : '');
        }

        if ($n < 100000) {
            $rem = $n % 1000;

            return $this->inrWordsHelper((int) floor($n / 1000)).' THOUSAND'.($rem ? ' '.$this->inrWordsHelper($rem) : '');
        }

        if ($n < 10000000) {
            $rem = $n % 100000;

            return $this->inrWordsHelper((int) floor($n / 100000)).' LAKH'.($rem ? ' '.$this->inrWordsHelper($rem) : '');
        }

        $rem = $n % 10000000;

        return $this->inrWordsHelper((int) floor($n / 10000000)).' CRORE'.($rem ? ' '.$this->inrWordsHelper($rem) : '');
    }

    private function westernWordsHelper(int $n): string
    {
        $ones = [
            0 => '', 1 => 'ONE', 2 => 'TWO', 3 => 'THREE', 4 => 'FOUR', 5 => 'FIVE',
            6 => 'SIX', 7 => 'SEVEN', 8 => 'EIGHT', 9 => 'NINE', 10 => 'TEN',
            11 => 'ELEVEN', 12 => 'TWELVE', 13 => 'THIRTEEN', 14 => 'FOURTEEN', 15 => 'FIFTEEN',
            16 => 'SIXTEEN', 17 => 'SEVENTEEN', 18 => 'EIGHTEEN', 19 => 'NINETEEN',
        ];

        $tens = [
            2 => 'TWENTY', 3 => 'THIRTY', 4 => 'FORTY', 5 => 'FIFTY',
            6 => 'SIXTY', 7 => 'SEVENTY', 8 => 'EIGHTY', 9 => 'NINETY',
        ];

        if ($n < 20) {
            return $ones[$n];
        }

        if ($n < 100) {
            $rem = $n % 10;

            return $tens[(int) floor($n / 10)].($rem ? ' '.$ones[$rem] : '');
        }

        if ($n < 1000) {
            $rem = $n % 100;

            return $ones[(int) floor($n / 100)].' HUNDRED'.($rem ? ' '.$this->westernWordsHelper($rem) : '');
        }

        if ($n < 1000000) {
            $rem = $n % 1000;

            return $this->westernWordsHelper((int) floor($n / 1000)).' THOUSAND'.($rem ? ' '.$this->westernWordsHelper($rem) : '');
        }

        $rem = $n % 1000000;

        return $this->westernWordsHelper((int) floor($n / 1000000)).' MILLION'.($rem ? ' '.$this->westernWordsHelper($rem) : '');
    }
}
