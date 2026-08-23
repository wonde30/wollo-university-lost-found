@extends('emails.layouts.base')

@section('content')
    <h2 style="margin-top: 0; color: #64748b; font-size: 18px; font-weight: 800;">Item Report Expired</h2>
    <p>Dear {{ $item->reporter->full_name ?? 'Reporter' }},</p>
    <p>Your item report has reached its university activity threshold and has been marked as <strong>Expired</strong>:</p>

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
            <td>Status</td>
            <td><span class="badge" style="background-color: #f1f5f9; color: #475569;">Expired / Archived</span></td>
        </tr>
    </table>

    <p style="font-size: 13px; color: #64748b;">
        This item has been removed from active public search listings. If you need assistance regarding this item, please visit the campus security office.
    </p>
@endsection
