<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_DONE = 'done';

    protected $fillable = ['colocation_id', 'title', 'due_date', 'assigned_to', 'status', 'completed_at', 'completed_by'];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function colocation()
    {
        return $this->belongsTo(Colocation::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at');
    }

    public function isDone(): bool
    {
        return $this->status === self::STATUS_DONE;
    }
}
