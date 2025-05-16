<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'credit', 'transaction_id', 'transaction_type', 'debit', 'admin_bonus', 'balance', 'reference'];

    // protected $casts = [
    //     'user_id' => 'integer',
    //     'credit' => 'float',
    //     'debit' => 'float',
    //     'admin_bonus'=>'float',
    //     'balance'=>'float',
    //     'reference'=>'string',
    //     'created_at'=>'string'
    // ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
