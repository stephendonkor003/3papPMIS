@extends('emails.layouts.notification', [
    'title' => 'Payment Receipt',
    'preheader' => 'Your procurement payment receipt is attached.',
    'actionUrl' => $portalUrl,
    'actionText' => 'Sign In to Vendor Portal',
    'accent' => '#059669',
])

@section('content')
    <p style="margin:0 0 15px;">Hello {{ $disbursement->vendor?->name ?? 'Vendor' }},</p>
    <p style="margin:0 0 18px;">A payment has been recorded and its receipt is attached to this email as a PDF.</p>

    <div style="border:1px solid #a7f3d0;border-radius:10px;background:#ecfdf5;padding:16px 18px;">
        <div style="margin-bottom:8px;"><strong>Receipt reference:</strong> {{ $disbursement->reference_no ?: 'Not assigned' }}</div>
        <div style="margin-bottom:8px;"><strong>Amount:</strong> {{ $disbursement->currency ?: 'USD' }} {{ number_format((float) $disbursement->amount, 2) }}</div>
        <div style="margin-bottom:8px;"><strong>Payment method:</strong> {{ \Illuminate\Support\Str::headline($disbursement->payment_method ?? 'Not specified') }}</div>
        @if ($disbursement->paid_at)
            <div><strong>Paid on:</strong> {{ $disbursement->paid_at->format('d M Y') }}</div>
        @endif
    </div>

    <p style="margin:18px 0 0;">Keep the attached receipt for your financial records.</p>
@endsection
