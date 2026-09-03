<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'code' => 'COMP-' . strtoupper($this->faker->unique()->lexify('????')),
            'legal_name' => $this->faker->company() . ' Telecoms Inc.',
            'trade_name' => $this->faker->company() . ' Broadband',
            'registration_number' => 'REG-' . $this->faker->numerify('########'),
            'tax_identifier' => 'TIN-' . $this->faker->numerify('###-###-###-000'),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'website' => 'https://' . $this->faker->domainName(),
            'address' => $this->faker->address(),
            'status' => 'ACTIVE',
        ];
    }
}
