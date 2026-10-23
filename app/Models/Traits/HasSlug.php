<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

/**
 * Generates a human-readable, URL-safe `slug` for a model on creation.
 *
 * The base text comes from {@see self::slugSourceValue()} (the `name` attribute
 * by default). Uniqueness is guaranteed by appending a numeric suffix
 * (`-2`, `-3`, …) when a collision is found. Models that belong to a business
 * scope the uniqueness check by `business_id`, so two tenants can share the
 * same slug without clashing.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            $model->ensureSlug();
        });

        static::updating(function ($model) {
            $source = $model->slugSource();

            if ($source !== null && $model->isDirty($source)) {
                $model->slug = $model->buildUniqueSlug();
            }
        });
    }

    protected function ensureSlug(): void
    {
        if (blank($this->slug)) {
            $this->slug = $this->buildUniqueSlug();
        }
    }

    /**
     * Build a unique slug for the current attribute values without persisting it.
     * Useful for backfilling rows created before the slug column existed.
     */
    public function generateSlug(): string
    {
        return $this->buildUniqueSlug();
    }

    /**
     * Attribute the slug is derived from. Return `null` to keep the slug fixed
     * once created (even if the source changes).
     */
    protected function slugSource(): ?string
    {
        return 'name';
    }

    protected function slugSourceValue(): string
    {
        $source = $this->slugSource();

        if ($source === null) {
            return '';
        }

        return self::stringifySlugValue($this->getAttribute($source));
    }

    /**
     * Normalise any attribute value (scalar or enum) to a plain string so it can
     * be turned into a slug.
     */
    protected static function stringifySlugValue(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        return (string) $value;
    }

    /**
     * The route key is the slug, so route()/route model binding use it instead
     * of the numeric id.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Column that scopes slug uniqueness. Tenant-aware models are scoped by
     * `business_id`; everyone else is globally unique.
     */
    protected function slugScopeColumn(): ?string
    {
        return in_array(BelongsToBusiness::class, class_uses_recursive(static::class), true)
            ? 'business_id'
            : null;
    }

    protected function buildUniqueSlug(): string
    {
        $base = Str::slug($this->slugSourceValue());

        if ($base === '') {
            $base = Str::slug(class_basename($this)) ?: 'registro';
        }

        $slug = $base;
        $suffix = 1;

        while ($this->slugExists($slug)) {
            $suffix++;
            $slug = $base.'-'.$suffix;
        }

        return $slug;
    }

    protected function slugExists(string $slug): bool
    {
        $query = static::query()
            ->withoutGlobalScopes()
            ->where('slug', $slug);

        if ($this->getKey() !== null) {
            $query->whereKeyNot($this->getKey());
        }

        if ($column = $this->slugScopeColumn()) {
            $query->where($column, $this->getAttribute($column));
        }

        return $query->exists();
    }
}
