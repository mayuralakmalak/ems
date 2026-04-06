@extends('layouts.frontend')

@section('title', 'Contact Us - ' . config('app.name', 'EMS'))

@push('styles')
<style>
        .hero-contact {
            padding: 60px 0 40px;
        }

        .hero-contact h1 {
            font-weight: 700;
            margin-bottom: 10px;
        }

        .hero-contact p {
            margin: 0;
            opacity: 0.9;
        }

        .contact-wrapper {
            margin-top: -40px;
            margin-bottom: 60px;
        }

        .contact-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15,23,42,0.08);
            padding: 30px;
        }

        .contact-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #64748b;
            margin-bottom: 4px;
        }

        .contact-input,
        .contact-textarea {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            font-size: 0.95rem;
        }

        .contact-input:focus,
        .contact-textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 1px rgba(99,102,241,0.25);
        }

        .btn-contact-submit {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-contact-submit:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: white;
        }

        .contact-info-card {
            background: #0f172a;
            color: #ffffff;
            border-radius: 16px;
            padding: 24px;
        }

        .contact-info-item {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
            align-items: flex-start;
        }

        .contact-info-icon {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(148,163,184,0.2);
        }

        .contact-info-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #ffffff;
        }

        .contact-info-card h5,
        .contact-info-card p,
        .contact-info-card .contact-info-label,
        .contact-info-card .contact-info-value {
            color: #ffffff !important;
        }

        .contact-info-icon i {
            color: #ffffff;
        }

        .contact-info-value {
            font-size: 0.95rem;
        }
    </style>
@endpush

@section('content')
<section class="hero-contact">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1>Contact Us</h1>
                <p>Have a question about exhibitions, bookings or payments? Send us a message.</p>
            </div>
        </div>
    </div>
</section>

<div class="container contact-wrapper">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="contact-card">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.submit') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="contact-label" for="name">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control contact-input"
                               value="{{ old('name', auth()->user()->name ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="contact-label" for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control contact-input"
                               value="{{ old('email', auth()->user()->email ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="contact-label" for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control contact-input"
                               value="{{ old('subject') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="contact-label" for="message">Message</label>
                        <textarea name="message" id="message" rows="5" class="form-control contact-textarea"
                                  required>{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-contact-submit">
                        <span>Send Message</span>
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="contact-info-card">
                <h5 class="mb-3">Need quick help?</h5>
                <p class="mb-4 text-sm text-light">
                    Our team will review your message and get back to you as soon as possible.
                </p>

                <div class="contact-info-item">
                    <div class="contact-info-icon">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <div>
                        <div class="contact-info-label">Support Email</div>
                        <div class="contact-info-value">
                            {{ config('mail.from.address') }}
                        </div>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <div class="contact-info-label">Response Time</div>
                        <div class="contact-info-value">
                            Typically within 1–2 business days
                        </div>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <div class="contact-info-label">Note</div>
                        <div class="contact-info-value">
                            For booking changes or urgent floorplan issues, please contact your assigned manager directly if available.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection