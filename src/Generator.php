<?php

namespace Slim\Example;

class Generator
{
    public static function generate($count)
    {
        $numbers = range(1, $count - 2);
        shuffle($numbers);

        $faker = \Faker\Factory::create();
        $faker->seed(1);
        $posts = [];
        for ($i = 0; $i < $count - 2; $i++) {
            $posts[] = [
                'id' => $fuker->uuid,
                'name' => $faker->text(70),
                'body' => $faker->sentence,
                'slug' => $faker->slug
            ];
        }

        return $posts;
    }
}
