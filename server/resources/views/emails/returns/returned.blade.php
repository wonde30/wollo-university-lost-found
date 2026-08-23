@extends('emails.layouts.base')

@section('content')
    <h2 style="margin-top: 0; color: #0F5132; font-size: 18px; font-weight: 800;">✅ Property Return Confirmed</h2>
    <p>Dear {{ $record->claim->claimant->full_name ?? 'Owner' }},</p>
    <p>This email confirms that your item has been successfully handed over to you by campus security/staff:</p>

    <table class="info-table">
        <tr>
            <td>Item Name</td>
            <td><strong>{{ $record->item->title ?? 'N/A' }}</strong></td>
        </tr>
        <tr>
            <td>Reference Code</td>
            <td><code>{{ $record->item->reference_code ?? 'N/A' }}</code></td>
        </tr>
        <tr>
            <td>Return Reference</td>
            <td><code>{{ $record->return_reference }}</code></td>
        </tr>
        <tr>
            <td>Handover Date</td>
            <td>{{ \Carbon\Carbon::parse($record->return_date)->format('M d, Y') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td><span class="badge badge-success">Returned & Closed</span></td>
        </tr>
    </table>

    <p style="font-size: 13px; color: #475569;">
        Thank you for using the Wollo University Property Recovery System. We are glad your property has been safely returned to you!
    </p>
@endsection
