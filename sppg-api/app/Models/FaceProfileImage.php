<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceProfileImage extends Model
{
    protected $fillable = [
        'face_profile_id',
        'image_path',
        'angle',
    ];

    public function faceProfile(): BelongsTo
    {
        return $this->belongsTo(FaceProfile::class);
    }
}