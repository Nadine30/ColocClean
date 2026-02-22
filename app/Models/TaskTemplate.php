<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['colocation_id', 'title'];

    public function colocation()
    {
        return $this->belongsTo(Colocation::class);
    }
}
