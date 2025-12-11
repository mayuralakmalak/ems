@extends('layouts.exhibitor')

@section('title', 'Edit Booking')
@section('page-title', 'Edit Booking')

@section('content')
<div class="container py-4">
    <div class="mb-3">
        <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Details
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Booking #{{ $booking->booking_number }}</h5>
            <small class="text-muted">Update contact emails and phone numbers.</small>
        </div>
        <div class="card-body">
            <form action="{{ route('bookings.update', $booking->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Contact Emails (max 5)</label>
                    <div id="emailFields">
                        @php $emails = $booking->contact_emails ?? []; @endphp
                        @if(empty($emails)) @php $emails = [auth()->user()->email]; @endphp @endif
                        @foreach($emails as $email)
                        <div class="input-group mb-2">
                            <input type="email" name="contact_emails[]" class="form-control" value="{{ $email }}" required>
                            <button type="button" class="btn btn-outline-danger remove-email"><i class="bi bi-trash"></i></button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addEmail">+ Add Email</button>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contact Numbers (max 5)</label>
                    <div id="phoneFields">
                        @php $numbers = $booking->contact_numbers ?? []; @endphp
                        @if(empty($numbers)) @php $numbers = [auth()->user()->phone]; @endphp @endif
                        @foreach($numbers as $number)
                        <div class="input-group mb-2">
                            <input type="text" name="contact_numbers[]" class="form-control" value="{{ $number }}" required>
                            <button type="button" class="btn btn-outline-danger remove-phone"><i class="bi bi-trash"></i></button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addPhone">+ Add Number</button>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Save Changes
                    </button>
                    <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const maxItems = 5;
    function addField(containerId, name, placeholder) {
        const container = document.getElementById(containerId);
        const count = container.querySelectorAll('input').length;
        if (count >= maxItems) return alert('Maximum ' + maxItems + ' entries allowed');
        const wrapper = document.createElement('div');
        wrapper.className = 'input-group mb-2';
        wrapper.innerHTML = `
            <input type="text" name="${name}" class="form-control" placeholder="${placeholder}" required>
            <button type="button" class="btn btn-outline-danger remove-btn"><i class="bi bi-trash"></i></button>
        `;
        container.appendChild(wrapper);
        wrapper.querySelector('.remove-btn').addEventListener('click', () => wrapper.remove());
    }

    document.getElementById('addEmail').addEventListener('click', () => addField('emailFields', 'contact_emails[]', 'Email'));
    document.getElementById('addPhone').addEventListener('click', () => addField('phoneFields', 'contact_numbers[]', 'Phone'));

    document.querySelectorAll('.remove-email').forEach(btn => {
        btn.addEventListener('click', () => btn.closest('.input-group').remove());
    });
    document.querySelectorAll('.remove-phone').forEach(btn => {
        btn.addEventListener('click', () => btn.closest('.input-group').remove());
    });
</script>
@endpush
@endsection
