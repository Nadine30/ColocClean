<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Colocation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'invitation_code', 'owner_id', 'user_id'];

    /**
     * Génère un code d'invitation unique (6 caractères alphanumériques majuscules).
     */
    public static function generateInvitationCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (self::where('invitation_code', $code)->exists());

        return $code;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'colocation_user')
            ->withTimestamps();
    }

    /**
     * Vérifie si un utilisateur est membre de la colocation.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}
