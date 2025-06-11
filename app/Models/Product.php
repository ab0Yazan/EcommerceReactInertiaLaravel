<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections('images')
            ->width(100);
        $this->addMediaConversion('small')
            ->performOnCollections('images')
            ->width(480);
        $this->addMediaConversion('large')
            ->performOnCollections('images')
            ->width(1200);
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function variationTypes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VariationType::class);
    }

    public function variations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    public function scopeForVendor(Builder $query): Builder
    {
        return $query->where('created_by', auth()->id());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ProductStatusEnum::Published);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function defaultImage(): string
    {
        return $this->getFirstMediaUrl('images', 'small') ?: asset('images/default_product.png');
    }



}
