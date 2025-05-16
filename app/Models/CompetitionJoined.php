<?php

namespace App\Models;

use App\Model\Seller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompetitionJoined extends Model
{
    use HasFactory;
    protected $table = 'competition_joined';
    protected $fillable = ['seller_id', 'competition_id'];
    public $timestamp = true;

    
    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }
}
