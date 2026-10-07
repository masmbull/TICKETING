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
     * Default 5-step "how to create a ticket" tour.
     *
     * `target` maps to a data-tour attribute rendered in the layout / create
     * form (spotlight highlight); `image` is the screenshot shown above the card
     * text. The default filenames ship in resources/tour (git-tracked, in the
     * image); admin uploads land in storage/app/private/tour, which the serve
     * route resolves first. A missing target is skipped client-side.
     */
    public static function defaultSteps(): array
    {
        return [
            [
                'title' => 'Cara buat tiket ke IT',
                'description' => 'Ikuti 5 langkah singkat berikut untuk mengirim tiket ke tim IT.',
                'target' => '',
                'image' => 'slide-1-dashboard.png',
            ],
            [
                'title' => 'Buka menu Create Ticket',
                'description' => 'Klik "Create Ticket" di sidebar untuk mulai membuat tiket baru.',
                'target' => 'create-ticket-link',
                'image' => 'slide-2-sidebar.png',
            ],
            [
                'title' => 'Pilih Category',
                'description' => 'Pilih category dan subcategory sesuai jenis masalah yang kamu alami.',
                'target' => 'ticket-category',
                'image' => 'slide-3-category.png',
            ],
            [
                'title' => 'Jelaskan masalahnya',
                'description' => 'Tulis deskripsi sedetail mungkin agar tim IT cepat memahami kendalamu.',
                'target' => 'ticket-description',
                'image' => 'slide-4-description.png',
            ],
            [
                'title' => 'Kirim tiket',
                'description' => 'Tekan "Submit Ticket" dan pantau perkembangannya di halaman My Tickets.',
                'target' => 'ticket-submit',
                'image' => 'slide-5-submit.png',
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