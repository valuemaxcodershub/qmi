<?php

namespace App\Models;

use App\Model\Seller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Competition extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $fillable = ['competition_name', 'competition_description', 'minimum_sales_amount', 'start_date', 'end_date', 'status'];

    public function seller_joined()
    {
        return $this->hasMany(CompetitionJoined::class);
    }

    public function hasSellerJoined($sellerId)
    {
        return $this->competitionJoined()->where('seller_id', $sellerId)->exists();
    }

    public function competitionJoined()
    {
        return $this->hasMany(CompetitionJoined::class, 'competition_id');
    }

    public function sellers()
    {
        return $this->belongsToMany(Seller::class, 'competition_joined', 'competition_id', 'seller_id');
    }
    
}
