<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Sale extends Model { protected $fillable = ['customer_id', 'user_id', 'subtotal', 'discount_total', 'total']; protected function casts(): array { return ['subtotal' => 'decimal:2', 'discount_total' => 'decimal:2', 'total' => 'decimal:2']; } public function items(): HasMany { return $this->hasMany(SaleItem::class); } public function customer(): BelongsTo { return $this->belongsTo(Customer::class); } public function seller(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); } }
