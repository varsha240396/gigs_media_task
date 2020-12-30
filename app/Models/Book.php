<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    protected $fillable = [
        'book_title',
        'description',
        'price',
        'book_image',
        'status',
        'user_id'
    ];
    function user(){
        return $this->belongsTo('App\Models\User');
    }
}
