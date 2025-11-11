<?php

declare(strict_types = 1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Seller\Category;
use App\Models\Seller\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'full_name',
        'person_type',
        'document',
        'phone',
        'acquirer_id',
        'preferred_acquirer',
        'cash_in_percentage',
        'cash_in_fixed',
        'cash_out_percentage',
        'cash_out_fixed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password'          => 'hashed',
            'cash_in_percentage' => 'decimal:2',
            'cash_in_fixed' => 'decimal:2',
            'cash_out_percentage' => 'decimal:2',
            'cash_out_fixed' => 'decimal:2',
        ];
    }

    /**
     * Set the avatar attribute - remove /storage/ prefix if present before saving.
     */
    public function setAvatarAttribute($value)
    {
        if (!$value) {
            $this->attributes['avatar'] = null;
            return;
        }

        // Se já é uma URL completa, extrair apenas o caminho
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $parsed = parse_url($value);
            $path = $parsed['path'] ?? '';
            // Remover /storage/ se presente
            $this->attributes['avatar'] = str_replace('/storage/', '', $path);
            return;
        }

        // Se tem /storage/, remover antes de salvar
        if (str_starts_with($value, '/storage/')) {
            $this->attributes['avatar'] = str_replace('/storage/', '', $value);
            return;
        }

        // Salvar como está (caminho relativo)
        $this->attributes['avatar'] = $value;
    }

    /**
     * Get the avatar URL attribute.
     */
    public function getAvatarAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // Se já é uma URL completa, retornar como está
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        // Se já tem /storage/, retornar como está
        if (str_starts_with($value, '/storage/')) {
            return $value;
        }

        // Caso contrário, adicionar /storage/
        return '/storage/' . $value;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /*
    * Relação com o endereço
    */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function acquirer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Acquirer::class);
    }

    public function financialSettings(): HasOne
    {
        return $this->hasOne(FinancialSetting::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function checkouts(): HasMany
    {
        return $this->hasMany(Checkout::class);
    }
}
