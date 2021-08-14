<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    use HasFactory;

    // 更新できるプロパティ
    protected $fillable = [
        'title', 'body', 'is_public', 'published_at'
    ];

    //  型変換
    protected $casts = [
        'is_public' => 'bool',
        'published_at' => 'datetime'
    ];

    //公開のみ表示
    public function scopePublic(Builder $query)
    {
        return $query->where('is_public', true);
    }

     // 公開記事一覧取得
     public function scopePublicList(Builder $query, string $tagSlug = null)
     {
         if ($tagSlug) {
             $query->whereHas('tags', function($query) use ($tagSlug) {
                $query->where('slug', $tagSlug);
             });
         }
         return $query
             ->public()
             ->with('user')
             ->latest('published_at')
             ->paginate(10);
     }

    // 公開記事をIDで取得
    public function scopePublicFindById(Builder $query, int $id)
    {
        return $query->public()->findOrFail($id);
    }

    // 公開日を年月日で表示
    public function getPublishedFormatAttribute()
    {
        return $this->published_at->format('Y年m月d日');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    protected static function boot()
    {
        parent::boot();
        self::saving(function($post) {
            $post->user_id = \Auth::id();
        });
    }
}
