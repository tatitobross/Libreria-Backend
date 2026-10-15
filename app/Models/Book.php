<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Book extends Model { protected $fillable = ['isbn', 'title', 'author_id', 'category_id', 'price', 'stock', 'description', 'active']; protected function casts(): array { return ['price' => 'decimal:2', 'active' => 'boolean']; } public function author(): BelongsTo { return $this->belongsTo(Author::class); } public function category(): BelongsTo { return $this->belongsTo(Category::class); } public function saleItems(): HasMany { return $this->hasMany(SaleItem::class); } }
