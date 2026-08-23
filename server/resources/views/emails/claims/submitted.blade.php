@extends('emails.layouts.base')

@section('content')
    <h2 style="margin-top: 0; color: #0F5132; font-size: 18px; font-weight: 800;">Claim Submission Received</h2>
    <p>Dear {{ $claim->claimant->full_name ?? 'Claimant' }},</p>
    <p>Your ownership claim for the item below has been successfully received and submitted for staff review:</p>

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
            <td>Claim ID</td>
            <td>#{{ $claim->id }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td><span class="badge badge-warning">Under Review</span></td>
        </tr>
        <tr>
            <td>Submission Date</td>
            <td>{{ now()->format('M d, Y H:i') }}</td>
        </tr>
    </table>

    <p style="font-size: 13px; color: #475569;">
        Campus security / custodial staff will verify your ownership description and supporting evidence. You will receive an update once a decision has been made.
    </p>
@endsection
