<?php

declare(strict_types = 1);

namespace App\Models\Seller;

use App\Enums\Seller\ProductTypeEnum;
use App\Models\Checkout;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasUuids;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'description',
        'image',
        'status',
        'type',
        'price',
        'stock',
    ];

    protected $casts = [
        'status' => 'boolean',
        'type'   => ProductTypeEnum::class,
        'price'  => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function checkout(): HasMany
    {
        return $this->hasMany(Checkout::class);
    }

    /**
     * Resolve route binding to ensure product belongs to authenticated user
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }
}
