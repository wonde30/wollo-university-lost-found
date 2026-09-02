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

    @if($record->confirmation_token && !$record->recipient_confirmed)
    <div style="margin: 24px 0; text-align: center;">
        <p style="margin-bottom: 12px; font-weight: bold; color: #1e293b;">Please confirm that you have safely received your item:</p>
        <a href="{{ config('app.frontend_url') }}/confirm-return/{{ $record->confirmation_token }}" 
           style="background-color: #0F5132; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">
            Confirm Collection Receipt
        </a>
        <p style="font-size: 11px; color: #64748b; margin-top: 8px;">This secure confirmation link is valid for 48 hours .</p>
    </div>
    @endif

    <p style="font-size: 13px; color: #475569;">
        Thank you for using the Wollo University Property Recovery System. We are glad your property has been safely returned to you!
    </p>
@endsection
