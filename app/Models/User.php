<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'user';
    protected $guarded = ['id'];

    public const CREATED_AT = 'createdAt';
    public const UPDATED_AT = 'updatedAt';

    protected $keyType = 'string';     // ID adalah string
    public $incrementing = false;      // Bukan auto increment

    // Untuk SoftDeletes
    public const DELETED_AT = 'deletedAt';

    protected $dates = [
        'createdAt',
        'updatedAt',
        'deletedAt'
    ];

    protected $hidden = [
        'password'
    ];
}
