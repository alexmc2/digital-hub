<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use Searchable;
    use HasFactory;

    // Include 'image' and 'topic' in the $fillable property
    protected $fillable = ['title', 'body', 'user_id', 'image', 'topic'];

    public function toSearchableArray()
    {

        return [
            'title' => $this->title,
            'body' => $this->body,
            'topic' => $this->topic,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
