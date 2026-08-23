@extends('emails.layouts.base')

@section('content')
    <h2 style="margin-top: 0; color: #0F5132; font-size: 18px; font-weight: 800;">🎉 Ownership Claim Approved!</h2>
    <p>Dear {{ $claim->claimant->full_name ?? 'Claimant' }},</p>
    <p>Great news! Your ownership claim for the following item has been <strong>approved</strong> by campus staff:</p>

    <table class="info-table">
        <tr>
            <td>Item Name</td>
            <td><strong>{{ $claim->item->title ?? 'N/A' }}</strong></td>
        </tr>
        <tr>
            <td>Reference Code</td>
            <td><code>{{ $claim->item->reference_code ?? 'N/A' }}</code></td>
        </tr>
        <tr>
            <td>Claim Status</td>
            <td><span class="badge badge-success">Approved</span></td>
        </tr>
        <tr>
            <td>Collection Location</td>
            <td>{{ $claim->item->location->name ?? $claim->item->campus->name ?? 'Campus Security Office' }}</td>
        </tr>
    </table>

    <h3 style="color: #0F5132; font-size: 14px; margin-top: 20px;">What to do next:</h3>
    <ol style="font-size: 13px; color: #334155; padding-left: 20px;">
        <li>Visit the designated campus security / custody office during working hours (8:30 AM – 5:30 PM).</li>
        <li>Present your official <strong>Wollo University Student/Staff ID card</strong>.</li>
        <li>Provide the reference code: <code>{{ $claim->item->reference_code ?? 'N/A' }}</code> to complete the physical handover confirmation.</li>
    </ol>
@endsection
