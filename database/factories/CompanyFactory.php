<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(App\Company::class, function (Faker $faker) {
	$company = $faker->company();
    return [
        'user_id'				=> 1,
        'document_type_id'		=> rand(1,2),
        'document_number'		=> $faker->randomNumber(),
        'name'                  => $company,
        'short_name'            => $company,
        'activity_date'			=> $faker->date('Y-m-d'),
        'perception_agent'		=> 18,
        'retention_agent'		=> 18,
        'state' 				=> rand(1,2),
    ];
});
