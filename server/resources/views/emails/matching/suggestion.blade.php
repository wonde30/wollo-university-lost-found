@extends('emails.layouts.base')

@section('content')
    <h2 style="margin-top: 0; color: #0F5132; font-size: 18px; font-weight: 800;">🔍 Potential Item Match Found!</h2>
    <p>Dear {{ $match->lostItem->reporter->full_name ?? 'Item Owner' }},</p>
    <p>Our automated campus matching engine has identified a potential match for your lost item report:</p>

    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; margin: 16px 0; text-align: center;">
        <span style="font-size: 12px; font-weight: bold; color: #166534; text-transform: uppercase;">Match Confidence</span>
        <div style="font-size: 28px; font-weight: 900; color: #0F5132; margin: 4px 0;">
            {{ round((float) $match->score * 100) }}%
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td>Your Lost Item</td>
            <td><strong>{{ $match->lostItem->title ?? 'N/A' }}</strong> (Ref: <code>{{ $match->lostItem->reference_code ?? 'N/A' }}</code>)</td>
        </tr>
        <tr>
            <td>Discovered Found Item</td>
            <td><strong>{{ $match->foundItem->title ?? 'N/A' }}</strong> (Ref: <code>{{ $match->foundItem->reference_code ?? 'N/A' }}</code>)</td>
        </tr>
        <tr>
            <td>Found Location</td>
            <td>{{ $match->foundItem->location->name ?? $match->foundItem->campus->name ?? 'Campus' }}</td>
        </tr>
    </table>

    <p style="font-size: 13px; color: #475569;">
        Please log in to your account, review the details and photos of the found item, and submit an ownership claim if this belongs to you.
    </p>
@endsection
