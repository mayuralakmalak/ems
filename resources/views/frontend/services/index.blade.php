@extends('layouts.exhibitor')

@section('title', 'Additional service booking')
@section('page-title', 'Additional service booking')

@push('styles')
<style>
    .service-booking-container {
        display: flex;
        gap: 25px;
    }
    
    .main-content {
        flex: 1;
    }
    
    .cart-sidebar {
        width: 350px;
        position: sticky;
        top: 20px;
        height: fit-content;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    
    .back-link {
        color: #6366f1;
        text-decoration: none;
        font-weight: 500;
    }
    
    .back-link:hover {
        text-decoration: underline;
    }
    
    .section-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 10px;
    }
    
    .section-subtitle {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 25px;
    }
    
    .category-section {
        margin-bottom: 40px;
    }
    
    .category-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .service-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }
    
    .service-image {
        width: 100%;
        height: 200px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }
    
    .service-image img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }
    
    .service-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 10px;
    }
    
    .service-description {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 15px;
    }
    
    .service-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: #6366f1;
        margin-bottom: 15px;
    }
    
    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .quantity-btn {
        width: 36px;
        height: 36px;
        border: 1px solid #cbd5e1;
        background: white;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .quantity-btn:hover {
        border-color: #6366f1;
        background: #f8fafc;
    }
    
    .quantity-input {
        width: 80px;
        padding: 8px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        text-align: center;
        font-weight: 500;
    }
    
    .quantity-input.out-of-stock {
        border-color: #ef4444;
        background: #fee2e2;
        color: #991b1b;
    }
    
    .btn-add-cart {
        width: 100%;
        padding: 12px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-add-cart:hover {
        background: #4f46e5;
    }
    
    .btn-add-cart:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
    }
    
    .cart-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }
    
    .cart-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .cart-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    
    .cart-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .cart-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
    }
    
    .cart-table tr:last-child td {
        border-bottom: none;
    }
    
    .cart-actions {
        display: flex;
        gap: 8px;
    }
    
    .action-icon {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .action-icon.edit {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .action-icon.delete {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .cart-total {
        padding: 20px;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .total-label {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .total-amount {
        font-size: 1.5rem;
        font-weight: 700;
        color: #6366f1;
    }
    
    .btn-proceed {
        width: 100%;
        padding: 15px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-proceed:hover {
        background: #4f46e5;
    }
    
    .btn-proceed:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h2 class="mb-2">Additional service booking</h2>
        <a href="{{ route('bookings.index') }}" class="back-link">
            <i class="bi bi-arrow-left me-1"></i>Go Back
        </a>
    </div>
    <div>
        <i class="bi bi-person-circle me-2" style="font-size: 1.5rem; color: #6366f1;"></i>
        <i class="bi bi-envelope" style="font-size: 1.5rem; color: #6366f1;"></i>
    </div>
</div>

<div class="service-booking-container">
    <!-- Main Content -->
    <div class="main-content">
        <h3 class="section-title">Book Additional Services</h3>
        <p class="section-subtitle">Room Utilities: For selected room: {{ $exhibition->name }}</p>
        
        @foreach($services as $category => $categoryServices)
        <div class="category-section">
            <h4 class="category-title">{{ $category ?: 'General Services' }}</h4>
            
            <div class="row g-4">
                @foreach($categoryServices as $service)
                <div class="col-md-6">
                    <div class="service-card">
                        <div class="service-image">
                            @if($service->image)
                            <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}">
                            @else
                            <i class="bi bi-image" style="font-size: 3rem; color: #cbd5e1;"></i>
                            @endif
                        </div>
                        
                        <div class="service-name">{{ $service->name }}</div>
                        <div class="service-description">{{ $service->description }}</div>
                        <div class="service-price">${{ number_format($service->price, 2) }}</div>
                        
                        <div class="quantity-selector">
                            <button class="quantity-btn" onclick="decreaseQuantity({{ $service->id }})">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" 
                                   class="quantity-input" 
                                   id="quantity_{{ $service->id }}" 
                                   value="{{ session('service_cart.' . $service->id, 0) }}" 
                                   min="0" 
                                   readonly>
                            <button class="quantity-btn" onclick="increaseQuantity({{ $service->id }})">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        
                        <button class="btn-add-cart" 
                                onclick="addToCart({{ $service->id }})"
                                id="btn_cart_{{ $service->id }}">
                            <i class="bi bi-cart-plus"></i>Add to cart
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Cart Sidebar -->
    <div class="cart-sidebar">
        <div class="cart-card">
            <h5 class="cart-title">Your Cart</h5>
            
            @if(count($cartItems) > 0)
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cartItems as $item)
                    <tr>
                        <td>{{ $item['service']->name }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>${{ number_format($item['total'], 2) }}</td>
                        <td>
                            <div class="cart-actions">
                                <div class="action-icon edit" onclick="editCartItem({{ $item['service']->id }})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </div>
                                <div class="action-icon delete" onclick="removeFromCart({{ $item['service']->id }})" title="Remove">
                                    <i class="bi bi-trash"></i>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="cart-total">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="total-label">Total</span>
                    <span class="total-amount" id="cartTotal">${{ number_format($cartTotal, 2) }}</span>
                </div>
            </div>
            
            <form action="{{ route('services.checkout') }}" method="POST">
                @csrf
                <input type="hidden" name="exhibition_id" value="{{ $exhibition->id }}">
                <button type="submit" class="btn-proceed">
                    Proceed to Payment
                </button>
            </form>
            @else
            <p class="text-muted text-center py-4">Your cart is empty</p>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function increaseQuantity(serviceId) {
    const input = document.getElementById(`quantity_${serviceId}`);
    const currentValue = parseInt(input.value) || 0;
    input.value = currentValue + 1;
    updateCartButton(serviceId);
}

function decreaseQuantity(serviceId) {
    const input = document.getElementById(`quantity_${serviceId}`);
    const currentValue = parseInt(input.value) || 0;
    if (currentValue > 0) {
        input.value = currentValue - 1;
        updateCartButton(serviceId);
    }
}

function updateCartButton(serviceId) {
    const input = document.getElementById(`quantity_${serviceId}`);
    const btn = document.getElementById(`btn_cart_${serviceId}`);
    const quantity = parseInt(input.value) || 0;
    
    if (quantity > 0) {
        btn.disabled = false;
    } else {
        btn.disabled = true;
    }
}

function addToCart(serviceId) {
    const input = document.getElementById(`quantity_${serviceId}`);
    const quantity = parseInt(input.value) || 0;
    
    if (quantity <= 0) {
        alert('Please select a quantity');
        return;
    }
    
    fetch('/ems-laravel/public/services/add-to-cart', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            service_id: serviceId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error adding to cart');
        }
    });
}

function removeFromCart(serviceId) {
    if (confirm('Remove this item from cart?')) {
        fetch('/ems-laravel/public/services/remove-from-cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                service_id: serviceId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function editCartItem(serviceId) {
    const input = document.getElementById(`quantity_${serviceId}`);
    input.focus();
    input.select();
}

// Initialize cart buttons
document.querySelectorAll('.quantity-input').forEach(input => {
    const serviceId = input.id.replace('quantity_', '');
    updateCartButton(serviceId);
});
</script>
@endpush
@endsection

