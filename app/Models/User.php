<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'theme'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = [
        'theme' => 'modern',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isRetro(): bool
    {
        return $this->theme === 'retro';
    }

    /**
     * O Orbit é um app desktop mono-usuário: garante que o usuário
     * local exista mesmo em um banco recém-criado (ex.: NativePHP
     * redireciona o SQLite para o diretório de dados do app).
     */
    public static function localUser(): self
    {

        return static::query()->oldest('id')->first()
            ?? static::create([
                'name' => '',
                'email' => 'local@orbit.app',
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(40)),
            ]);
    }
}
