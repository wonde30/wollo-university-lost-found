@extends('emails.layouts.base')

@section('content')
    <h2 style="margin-top: 0; color: #b45309; font-size: 18px; font-weight: 800;">⚠️ Notice: Item Listing Expiry Warning</h2>
    <p>Dear {{ $item->reporter->full_name ?? 'Reporter' }},</p>
    <p>This is a courtesy reminder that your reported item will reach its active listing expiry threshold soon:</p>

    <table class="info-table">
        <tr>
            <td>Item Name</td>
            <td><strong>{{ $item->title }}</strong></td>
        </tr>
        <tr>
            <td>Reference Code</td>
            <td><code>{{ $item->reference_code }}</code></td>
        </tr>
        <tr>
            <td>Time Remaining</td>
            <td><strong style="color: #b45309;">{{ $daysRemaining ?? 5 }} Days</strong></td>
        </tr>
    </table>

    <p style="font-size: 13px; color: #475569;">
        If you have already recovered your belongings or wish to update the status of this report, please log in to your dashboard.
    </p>
@endsection
