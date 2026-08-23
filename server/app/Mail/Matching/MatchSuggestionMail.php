<?php

declare(strict_types=1);

namespace App\Mail\Matching;

use App\Models\MatchSuggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MatchSuggestionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly MatchSuggestion $match)
    {}

    public function build(): self
    {
        $this->match->loadMissing([
            'lostItem.reporter',
            'foundItem.location',
            'foundItem.campus',
        ]);

        $score = round((float) $this->match->score * 100);

        return $this->subject("Wollo Lost & Found - Potential Match Found ({$score}% Match)")
            ->view('emails.matching.suggestion', [
                'match' => $this->match,
            ]);
    }
}
