<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['code', 'title', 'credits', 'description'];

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
