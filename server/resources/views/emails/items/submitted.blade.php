@extends('emails.layouts.base')

@section('content')
    <h2>Item Report Submitted</h2>
    <p>Dear {{ $userName ?? 'User' }},</p>
    <p>Your item report (Reference: <strong>{{ $referenceCode ?? 'N/A' }}</strong>) has been received.</p>
    <p>We will notify you when matching items or updates are available.</p>
@endsection
