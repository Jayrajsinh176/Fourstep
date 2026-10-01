<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdCard extends Model
{
    use HasFactory;

    protected $table = 'id_cards';

    protected $fillable = [
        'member_id',
        'user_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];
}
