<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class TicketComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'comment',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class, 'comment_id');
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(CommentMention::class, 'comment_id');
    }

    public function mentionedUsers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            CommentMention::class,
            'comment_id',
            'id',
            'id',
            'user_id'
        );
    }
}
