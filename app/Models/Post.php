<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use App\Models\Concerns\FormatsDateTimes;

/**
 * Klass Post
 *
 * Esindab blogipostitust.
 * Postitusi saab luua ja muuta ainult autor (admin).
 * Kõik kasutajad saavad postitusi lugeda.
 */
class Post extends Model {
    use AsSource, Filterable, Attachable, FormatsDateTimes;
    /**
     * Massmääramisega lubatud väljad.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'intro',
        'body_html',
        'published_at',
        'featured_image_path',
    ];

    /**
     * Lõpus olevad meetodid vormindamiseks
     * 
     * @var array
     */
    protected $appends = [
        'published_at_formatted',
        'updated_at_formatted',
        'created_at_formatted',
        'published_at_date',
    ];

    /**
     * Tüübimuundused andmebaasist lugemisel.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',        
    ];

    /**
     * Postituse autor (kasutaja).
     *
     * @return BelongsTo
     */
    public function author(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Postitusega seotud kommentaarid.
     *
     * @return HasMany
     */
    public function comments(): HasMany {
        return $this->hasMany(Comment::class);
    }

    /**
     * Postitusega seotud reaktsioonid (meeldimised).
     *
     * @return HasMany
     */
    public function reactions(): HasMany {
        return $this->hasMany(Reaction::class);
    }

    /**
     * Konkreetse sisseloginud kasutaja reaktsioon sellele postitusele.
     * Kasutus: $post->myReaction (eeldab eager-loadi koos user_id filtriga).
     */
    public function myReaction(): HasOne {
        return $this->hasOne(Reaction::class)->where('user_id', Auth::id());
    }
    /**
     * Scope avaldatud postituste filtreerimiseks.
     *
     * Tagastab ainult need postitused:
     * - mis on märgitud avaldatuks (`is_published = true`)
     * - mille avaldamise kuupäev (`published_at`) on tänane või varasem
     *
     * Seda scope'i kasutatakse avalikus vaates, et:
     * - tulevikku ajastatud postitusi mitte kuvada
     * - peidetud (avaldamata) postitusi mitte näidata
     *
     * Kasutusnäide:
     * Post::published()->latest()->paginate(10);
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopePublished(Builder $query): Builder {
        return $query
            ->whereNotNull('published_at')
            ->whereDate('published_at', '<=', now());
    }

    /**
     * Scope avaldamata või tulevikku ajastatud postituste leidmiseks.
     *
     * Tagastab postitused, mis:
     * - ei ole veel avaldatud (`is_published = false`)
     *   VÕI
     * - mille avaldamise kuupäev on tulevikus
     *
     * Kasulik admin-paneelis mustandite ja ajastatud postituste kuvamiseks.
     *
     * Kasutusnäide:
     * Post::unpublished()->orderBy('published_at')->get();
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeUnpublished(Builder $query): Builder {
        return $query->whereNull('published_at');
    }

    /**
     * Kontrollib, kas postitusel on featured pilt määratud.
     */
    public function hasFeaturedImage(): bool {        
        return !empty($this->featured_image_path);
    }

    /**
     * Tagastab featured pildi URL-i.
     * Kui pilti pole määratud, tagastab default pildi URL-i.
     */
    public function featuredImageUrl(): string {
        if (!$this->hasFeaturedImage()) {
            return asset('images/post-default.png');
        }

        $path = ltrim($this->featured_image_path, '/');

        // Kui DB-s on kogemata "storage/posts/..." -> tee "posts/..."
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        // Kui DB-s on kogemata juba "/storage/posts/..." -> tagasta otse
        if (str_starts_with($this->featured_image_path, '/storage/')) {
            return url($this->featured_image_path);
        }

        // Standard: DB-s "posts/..." -> URL "/storage/posts/..."
        return asset('storage/' . $path);
    }


    public function getPublishedAtFormattedAttribute(): ?string {
        return $this->formatDateTime($this->published_at);
    }

    public function getCreatedAtFormattedAttribute(): ?string {
        return $this->formatDateTime($this->created_at);
    }

    public function getUpdatedAtFormattedAttribute(): ?string {
        return $this->formatDateTime($this->updated_at);
    }
    public function getPublishedAtDateAttribute(): ?string {
        return $this->published_at?->format('d.m.Y');
    }
}
