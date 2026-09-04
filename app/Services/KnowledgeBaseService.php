<?php

namespace App\Services;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use Illuminate\Support\Str;

class KnowledgeBaseService
{
    public function publishArticle(
        string $title,
        int $categoryId,
        string $content,
        string $visibility = 'CUSTOMER',
        ?int $authorId = null
    ): KnowledgeArticle {
        $slug = Str::slug($title) . '-' . Str::random(5);

        return KnowledgeArticle::create([
            'title' => $title,
            'slug' => $slug,
            'category_id' => $categoryId,
            'content' => $content,
            'visibility' => $visibility,
            'is_published' => true,
            'author_id' => $authorId,
        ]);
    }

    public function searchArticles(string $query, string $visibility = 'CUSTOMER')
    {
        return KnowledgeArticle::where('is_published', true)
            ->where(function($q) use ($visibility) {
                if ($visibility === 'CUSTOMER') {
                    $q->whereIn('visibility', ['CUSTOMER', 'BOTH']);
                }
            })
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->get();
    }
}
