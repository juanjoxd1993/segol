<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(DocumentTypesTableSeeder::class);
        $this->call(VoucherTypesTableSeeder::class);
        $this->call(UnitsTableSeeder::class);
        $this->call(CompaniesTableSeeder::class);
        $this->call(CompanyAddressesTableSeeder::class);
        $this->call(PaymentsTableSeeder::class);
        $this->call(CurrenciesTableSeeder::class);
        $this->call(AddressTypesTableSeeder::class);
        $this->call(CreditNoteReasonsTableSeeder::class);
    }
}
