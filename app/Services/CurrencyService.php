<?php

namespace App\Services;

class CurrencyService
{
    protected array $countries = [
        'Nigeria' => ['code' => 'NGN', 'symbol' => '₦', 'rate' => 1600],
        'Kenya' => ['code' => 'KES', 'symbol' => 'KSh', 'rate' => 131],
        'Ghana' => ['code' => 'GHS', 'symbol' => 'GH₵', 'rate' => 14.5],
        'South Africa' => ['code' => 'ZAR', 'symbol' => 'R', 'rate' => 18.5],
        'United Kingdom' => ['code' => 'GBP', 'symbol' => '£', 'rate' => 0.79],
        'Europe' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Tanzania' => ['code' => 'TZS', 'symbol' => 'TSh', 'rate' => 2600],
        'Uganda' => ['code' => 'UGX', 'symbol' => 'USh', 'rate' => 3800],
        'Zambia' => ['code' => 'ZMW', 'symbol' => 'ZK', 'rate' => 27],
        'Rwanda' => ['code' => 'RWF', 'symbol' => 'FRw', 'rate' => 1300],
        'United States' => ['code' => 'USD', 'symbol' => '$', 'rate' => 1],
        'India' => ['code' => 'INR', 'symbol' => '₹', 'rate' => 83],
        'Canada' => ['code' => 'CAD', 'symbol' => 'C$', 'rate' => 1.37],
        'Australia' => ['code' => 'AUD', 'symbol' => 'A$', 'rate' => 1.54],
        'Brazil' => ['code' => 'BRL', 'symbol' => 'R$', 'rate' => 5.0],
        'Egypt' => ['code' => 'EGP', 'symbol' => 'E£', 'rate' => 48],
        'Mexico' => ['code' => 'MXN', 'symbol' => 'Mex$', 'rate' => 18],
        'Philippines' => ['code' => 'PHP', 'symbol' => '₱', 'rate' => 56],
        'Pakistan' => ['code' => 'PKR', 'symbol' => '₨', 'rate' => 280],
        'Bangladesh' => ['code' => 'BDT', 'symbol' => '৳', 'rate' => 110],
        'Indonesia' => ['code' => 'IDR', 'symbol' => 'Rp', 'rate' => 15700],
        'Thailand' => ['code' => 'THB', 'symbol' => '฿', 'rate' => 35],
        'Vietnam' => ['code' => 'VND', 'symbol' => '₫', 'rate' => 25400],
        'United Arab Emirates' => ['code' => 'AED', 'symbol' => 'د.إ', 'rate' => 3.67],
        'Saudi Arabia' => ['code' => 'SAR', 'symbol' => '﷼', 'rate' => 3.75],
        'Switzerland' => ['code' => 'CHF', 'symbol' => 'Fr', 'rate' => 0.88],
        'Sweden' => ['code' => 'SEK', 'symbol' => 'kr', 'rate' => 10.5],
        'Norway' => ['code' => 'NOK', 'symbol' => 'kr', 'rate' => 10.7],
        'Denmark' => ['code' => 'DKK', 'symbol' => 'kr', 'rate' => 6.9],
        'Poland' => ['code' => 'PLN', 'symbol' => 'zł', 'rate' => 4.0],
        'Turkey' => ['code' => 'TRY', 'symbol' => '₺', 'rate' => 32],
        'Malaysia' => ['code' => 'MYR', 'symbol' => 'RM', 'rate' => 4.7],
        'Singapore' => ['code' => 'SGD', 'symbol' => 'S$', 'rate' => 1.35],
        'China' => ['code' => 'CNY', 'symbol' => '¥', 'rate' => 7.2],
        'Japan' => ['code' => 'JPY', 'symbol' => '¥', 'rate' => 150],
        'South Korea' => ['code' => 'KRW', 'symbol' => '₩', 'rate' => 1350],
        'Morocco' => ['code' => 'MAD', 'symbol' => 'MAD', 'rate' => 10.2],
        'Algeria' => ['code' => 'DZD', 'symbol' => 'د.ج', 'rate' => 135],
        'Angola' => ['code' => 'AOA', 'symbol' => 'Kz', 'rate' => 825],
        'Ethiopia' => ['code' => 'ETB', 'symbol' => 'Br', 'rate' => 57],
        'Mozambique' => ['code' => 'MZN', 'symbol' => 'MT', 'rate' => 64],
        'Botswana' => ['code' => 'BWP', 'symbol' => 'P', 'rate' => 13.5],
        'Namibia' => ['code' => 'NAD', 'symbol' => 'N$', 'rate' => 18.5],
        'Malawi' => ['code' => 'MWK', 'symbol' => 'MK', 'rate' => 1740],
        'Seychelles' => ['code' => 'SCR', 'symbol' => '₨', 'rate' => 14],
        'Mauritius' => ['code' => 'MUR', 'symbol' => '₨', 'rate' => 46],
        'Côte d\'Ivoire' => ['code' => 'XOF', 'symbol' => 'CFA', 'rate' => 605],
        'Senegal' => ['code' => 'XOF', 'symbol' => 'CFA', 'rate' => 605],
        'Cameroon' => ['code' => 'XAF', 'symbol' => 'FCFA', 'rate' => 605],
        'Congo' => ['code' => 'XAF', 'symbol' => 'FCFA', 'rate' => 605],
        'Qatar' => ['code' => 'QAR', 'symbol' => '﷼', 'rate' => 3.64],
        'Kuwait' => ['code' => 'KWD', 'symbol' => 'د.ك', 'rate' => 0.31],
        'Oman' => ['code' => 'OMR', 'symbol' => '﷼', 'rate' => 0.38],
        'Bahrain' => ['code' => 'BHD', 'symbol' => '.د.ب', 'rate' => 0.38],
        'Israel' => ['code' => 'ILS', 'symbol' => '₪', 'rate' => 3.7],
        'Chile' => ['code' => 'CLP', 'symbol' => 'CLP$', 'rate' => 950],
        'Colombia' => ['code' => 'COP', 'symbol' => 'COL$', 'rate' => 4050],
        'Argentina' => ['code' => 'ARS', 'symbol' => 'AR$', 'rate' => 880],
        'Peru' => ['code' => 'PEN', 'symbol' => 'S/', 'rate' => 3.75],
        'New Zealand' => ['code' => 'NZD', 'symbol' => 'NZ$', 'rate' => 1.64],
        'Russia' => ['code' => 'RUB', 'symbol' => '₽', 'rate' => 92],
        'Ukraine' => ['code' => 'UAH', 'symbol' => '₴', 'rate' => 39],
        'Romania' => ['code' => 'RON', 'symbol' => 'lei', 'rate' => 4.6],
        'Czech Republic' => ['code' => 'CZK', 'symbol' => 'Kč', 'rate' => 23],
        'Hungary' => ['code' => 'HUF', 'symbol' => 'Ft', 'rate' => 365],
        'Croatia' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Greece' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Portugal' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Spain' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'France' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Germany' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Italy' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Netherlands' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Belgium' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Austria' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Ireland' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Finland' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Slovakia' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Slovenia' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Estonia' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Latvia' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Lithuania' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Luxembourg' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Malta' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'Cyprus' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
    ];

    public function getCurrencyCode(?string $country): string
    {
        return $this->countries[$country]['code'] ?? 'USD';
    }

    public function getCurrencySymbol(?string $country): string
    {
        return $this->countries[$country]['symbol'] ?? '$';
    }

    public function getRate(?string $country): float
    {
        return $this->countries[$country]['rate'] ?? 1.0;
    }

    public function convert(float $amount, ?string $country): float
    {
        return round($amount * $this->getRate($country), 2);
    }

    public function format(float $amount, ?string $country): string
    {
        $symbol = $this->getCurrencySymbol($country);
        return $symbol . number_format($this->convert($amount, $country), 0);
    }

    public function getConfig(?string $country): array
    {
        if ($country && isset($this->countries[$country])) {
            return $this->countries[$country];
        }
        return ['code' => 'USD', 'symbol' => '$', 'rate' => 1.0];
    }

    public function all(): array
    {
        return $this->countries;
    }
}
