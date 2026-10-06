<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [

            /*
            |--------------------------------------------------------------------------
            | Dhaka Division - 13 Districts
            |--------------------------------------------------------------------------
            */

            ['division' => 'Dhaka', 'district' => 'Dhaka'],
            ['division' => 'Dhaka', 'district' => 'Gazipur'],
            ['division' => 'Dhaka', 'district' => 'Kishoreganj'],
            ['division' => 'Dhaka', 'district' => 'Manikganj'],
            ['division' => 'Dhaka', 'district' => 'Munshiganj'],
            ['division' => 'Dhaka', 'district' => 'Narayanganj'],
            ['division' => 'Dhaka', 'district' => 'Narsingdi'],
            ['division' => 'Dhaka', 'district' => 'Tangail'],
            ['division' => 'Dhaka', 'district' => 'Faridpur'],
            ['division' => 'Dhaka', 'district' => 'Gopalganj'],
            ['division' => 'Dhaka', 'district' => 'Madaripur'],
            ['division' => 'Dhaka', 'district' => 'Rajbari'],
            ['division' => 'Dhaka', 'district' => 'Shariatpur'],

            /*
            |--------------------------------------------------------------------------
            | Chattogram Division - 11 Districts
            |--------------------------------------------------------------------------
            */

            ['division' => 'Chattogram', 'district' => 'Chattogram'],
            ['division' => 'Chattogram', 'district' => "Cox's Bazar"],
            ['division' => 'Chattogram', 'district' => 'Cumilla'],
            ['division' => 'Chattogram', 'district' => 'Brahmanbaria'],
            ['division' => 'Chattogram', 'district' => 'Chandpur'],
            ['division' => 'Chattogram', 'district' => 'Feni'],
            ['division' => 'Chattogram', 'district' => 'Lakshmipur'],
            ['division' => 'Chattogram', 'district' => 'Noakhali'],
            ['division' => 'Chattogram', 'district' => 'Khagrachhari'],
            ['division' => 'Chattogram', 'district' => 'Rangamati'],
            ['division' => 'Chattogram', 'district' => 'Bandarban'],

            /*
            |--------------------------------------------------------------------------
            | Rajshahi Division - 8 Districts
            |--------------------------------------------------------------------------
            */

            ['division' => 'Rajshahi', 'district' => 'Rajshahi'],
            ['division' => 'Rajshahi', 'district' => 'Bogura'],
            ['division' => 'Rajshahi', 'district' => 'Joypurhat'],
            ['division' => 'Rajshahi', 'district' => 'Naogaon'],
            ['division' => 'Rajshahi', 'district' => 'Natore'],
            ['division' => 'Rajshahi', 'district' => 'Chapainawabganj'],
            ['division' => 'Rajshahi', 'district' => 'Pabna'],
            ['division' => 'Rajshahi', 'district' => 'Sirajganj'],

            /*
            |--------------------------------------------------------------------------
            | Khulna Division - 10 Districts
            |--------------------------------------------------------------------------
            */

            ['division' => 'Khulna', 'district' => 'Khulna'],
            ['division' => 'Khulna', 'district' => 'Bagerhat'],
            ['division' => 'Khulna', 'district' => 'Chuadanga'],
            ['division' => 'Khulna', 'district' => 'Jashore'],
            ['division' => 'Khulna', 'district' => 'Jhenaidah'],
            ['division' => 'Khulna', 'district' => 'Kushtia'],
            ['division' => 'Khulna', 'district' => 'Magura'],
            ['division' => 'Khulna', 'district' => 'Meherpur'],
            ['division' => 'Khulna', 'district' => 'Narail'],
            ['division' => 'Khulna', 'district' => 'Satkhira'],

            /*
            |--------------------------------------------------------------------------
            | Barishal Division - 6 Districts
            |--------------------------------------------------------------------------
            */

            ['division' => 'Barishal', 'district' => 'Barishal'],
            ['division' => 'Barishal', 'district' => 'Barguna'],
            ['division' => 'Barishal', 'district' => 'Bhola'],
            ['division' => 'Barishal', 'district' => 'Jhalokathi'],
            ['division' => 'Barishal', 'district' => 'Patuakhali'],
            ['division' => 'Barishal', 'district' => 'Pirojpur'],

            /*
            |--------------------------------------------------------------------------
            | Sylhet Division - 4 Districts
            |--------------------------------------------------------------------------
            */

            ['division' => 'Sylhet', 'district' => 'Sylhet'],
            ['division' => 'Sylhet', 'district' => 'Habiganj'],
            ['division' => 'Sylhet', 'district' => 'Moulvibazar'],
            ['division' => 'Sylhet', 'district' => 'Sunamganj'],

            /*
            |--------------------------------------------------------------------------
            | Rangpur Division - 8 Districts
            |--------------------------------------------------------------------------
            */

            ['division' => 'Rangpur', 'district' => 'Rangpur'],
            ['division' => 'Rangpur', 'district' => 'Dinajpur'],
            ['division' => 'Rangpur', 'district' => 'Gaibandha'],
            ['division' => 'Rangpur', 'district' => 'Kurigram'],
            ['division' => 'Rangpur', 'district' => 'Lalmonirhat'],
            ['division' => 'Rangpur', 'district' => 'Nilphamari'],
            ['division' => 'Rangpur', 'district' => 'Panchagarh'],
            ['division' => 'Rangpur', 'district' => 'Thakurgaon'],

            /*
            |--------------------------------------------------------------------------
            | Mymensingh Division - 4 Districts
            |--------------------------------------------------------------------------
            */

            ['division' => 'Mymensingh', 'district' => 'Mymensingh'],
            ['division' => 'Mymensingh', 'district' => 'Jamalpur'],
            ['division' => 'Mymensingh', 'district' => 'Netrokona'],
            ['division' => 'Mymensingh', 'district' => 'Sherpur'],
        ];

        foreach ($locations as $location) {
            $slug = Str::slug(
                $location['division']
                . '-'
                . $location['district']
            );

            Location::updateOrCreate(
                [
                    'slug' => $slug,
                ],
                [
                    'division' => $location['division'],
                    'district' => $location['district'],
                    'area' => $location['district'],
                    'status' => true,
                ]
            );
        }
    }
}