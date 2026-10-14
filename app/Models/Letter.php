<?php

namespace App\Models;

use App\Actions\SyncMemberTransferFromChangeLetter;
use App\Enums\LetterType;
use Database\Factories\LetterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Letter extends Model
{
    /** @use HasFactory<LetterFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'member_id',
        'type',
        'issued_at',
        'destination',
        'directed_by_member_id',
        'member_spiritual_situation_id',
    ];

    protected static function booted(): void
    {
        static::saved(function (Letter $letter): void {
            app(SyncMemberTransferFromChangeLetter::class)->handle($letter);
        });

        static::deleting(function (Letter $letter): void {
            app(SyncMemberTransferFromChangeLetter::class)->discardOnDelete($letter);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => LetterType::class,
            'issued_at' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function directedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'directed_by_member_id');
    }

    public function transferSituation(): BelongsTo
    {
        return $this->belongsTo(MemberSpiritualSituation::class, 'member_spiritual_situation_id');
    }
}
