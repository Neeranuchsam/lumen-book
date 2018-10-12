<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BooksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\Author', 10)->create()->each(function ($author) {
            $author->ratings()->saveMany(
                factory('App\Rating', rand(20, 50))->make()
            );

            $booksCount = rand(1, 5);
            while ($booksCount > 0) {
                $author->books()->save(factory('App\Book')->make());
                $booksCount--;
            }
        });
    }
}
