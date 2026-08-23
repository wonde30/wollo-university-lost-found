@extends('emails.layouts.base')

@section('content')
    <h2 style="margin-top: 0; color: #dc2626; font-size: 18px; font-weight: 800;">Claim Review Update</h2>
    <p>Dear {{ $claim->claimant->full_name ?? 'Claimant' }},</p>
    <p>Thank you for submitting a claim. Following administrative verification, your ownership claim for the following item could not be approved at this time:</p>

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
            <td><span class="badge" style="background-color: #fee2e2; color: #991b1b;">Rejected</span></td>
        </tr>
        @if(!empty($claim->review_note))
        <tr>
            <td>Staff Feedback</td>
            <td><em>{{ $claim->review_note }}</em></td>
        </tr>
        @endif
    </table>

    <p style="font-size: 13px; color: #64748b;">
        If you believe this decision was made in error or if you have additional identifying documentation (serial numbers, receipts, matching photos), please visit the campus security office in person.
    </p>
@endsection
