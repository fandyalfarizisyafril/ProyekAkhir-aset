<?php

namespace Database\Seeders;

use App\Support\DefaultBidang;
use Illuminate\Database\Seeder;

class BidangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DefaultBidang::ensure();
    }
}
