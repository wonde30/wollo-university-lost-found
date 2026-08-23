@extends('emails.layouts.base')

@section('content')
    <h2 style="margin-top: 0; color: #0F5132; font-size: 18px; font-weight: 800;">Item Report Confirmed</h2>
    <p>Dear {{ $item->reporter->full_name ?? 'Reporter' }},</p>
    <p>Your {{ $item->type === 'found' ? 'found property report' : 'lost property report' }} has been successfully recorded in the campus registry:</p>

    <table class="info-table">
        <tr>
            <td>Item Name</td>
            <td><strong>{{ $item->title }}</strong></td>
        </tr>
        <tr>
            <td>Reference Code</td>
            <td><code style="font-size: 14px; font-weight: bold;">{{ $item->reference_code }}</code></td>
        </tr>
        <tr>
            <td>Report Type</td>
            <td><span class="badge badge-info">{{ ucfirst($item->type instanceof \BackedEnum ? $item->type->value : $item->type) }}</span></td>
        </tr>
        <tr>
            <td>Category</td>
            <td>{{ $item->category->name ?? 'General' }}</td>
        </tr>
        <tr>
            <td>Location</td>
            <td>{{ $item->location->name ?? $item->campus->name ?? 'Campus Grounds' }}</td>
        </tr>
    </table>

    <p style="font-size: 13px; color: #475569;">
        Keep your reference code (<strong>{{ $item->reference_code }}</strong>) safe. You can use it anytime to publicly track the status of your item or manage claims.
    </p>
@endsection
