<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    protected $table = "bundles";
    protected $fillable = ['title', 'description'];

    public function books()
    {
        return $this->belongsToMany(Book::class);
    }
}
