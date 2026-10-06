<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TooltipSetting extends Model
{
    protected $fillable = ['enabled', 'steps'];

    protected $casts = [
        'enabled' => 'boolean',
        'steps' => 'array',
    ];

    /**
     * Default 5-step "how to create a ticket" tour. `target` maps to a
     * data-tour attribute rendered in the layout / create-ticket form;
     * a missing target is skipped client-side.
     */
    public static function defaultSteps(): array
    {
        return [
            [
                'title' => 'Cara buat tiket ke IT',
                'description' => 'Ikuti 5 langkah singkat berikut untuk mengirim tiket ke tim IT.',
                'target' => '',
            ],
            [
                'title' => 'Buka menu Create Ticket',
                'description' => 'Klik "Create Ticket" di sidebar untuk mulai membuat tiket baru.',
                'target' => 'create-ticket-link',
            ],
            [
                'title' => 'Pilih Category',
                'description' => 'Pilih category dan subcategory sesuai jenis masalah yang kamu alami.',
                'target' => 'ticket-category',
            ],
            [
                'title' => 'Jelaskan masalahnya',
                'description' => 'Tulis deskripsi sedetail mungkin agar tim IT cepat memahami kendalamu.',
                'target' => 'ticket-description',
            ],
            [
                'title' => 'Kirim tiket',
                'description' => 'Tekan "Submit Ticket" dan pantau perkembangannya di halaman My Tickets.',
                'target' => 'ticket-submit',
            ],
        ];
    }

    /**
     * The single settings row (there is only ever one), created on demand.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'enabled' => false,
            'steps' => static::defaultSteps(),
        ]);
    }
}