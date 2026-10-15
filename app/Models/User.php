<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
 
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
 
    protected $fillable = [
        'username', 'password', 'name', 'gender',
        'birth', 'email', 'status', 'role_id',
    ];
 
    protected $hidden = ['password', 'remember_token'];
 
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth' => 'date',
            'password' => 'hashed',
        ];
    }
 
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role?->role, $roles, true);
    }
}
