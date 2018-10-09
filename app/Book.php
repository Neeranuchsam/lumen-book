<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = "books";
    /**
     * The attributes that are mass assignable
     * @var array
     */
    protected $fillable = ['title', 'description', 'author_id'];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}
