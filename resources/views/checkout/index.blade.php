@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">{{ __('checkout.checkout') }}</h1>
            <p class="mt-2 text-gray-400">{{ __('checkout.complete_purchase') }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Billing Form -->
            <div class="lg:col-span-2">
                <form id="checkout-form" class="bg-gray-800 rounded-lg shadow-lg p-6">
                    @csrf
                    
                    <!-- Hidden Fields -->
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <input type="hidden" name="billing_period" value="{{ $billingPeriod }}">
                    <input type="hidden" id="payment-intent-id" name="payment_intent_id" value="">

                    <!-- Billing Information -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-white mb-4">{{ __('checkout.billing_information') }}</h2>
                        
                        <div class="space-y-4">
                            <!-- Company Name -->
                            <div>
                                <label for="company" class="block text-sm font-medium text-gray-300 mb-1">
                                    {{ __('checkout.company_name') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="company" name="company" required
                                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="{{ __('checkout.company_name_placeholder') }}"
                                    value="{{ old('company', $organization->company ?? '') }}">
                            </div>

                            <!-- Billing Email -->
                            <div>
                                <label for="billing_email" class="block text-sm font-medium text-gray-300 mb-1">
                                    {{ __('checkout.billing_email') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="billing_email" name="billing_email" required
                                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="{{ __('checkout.billing_email_placeholder') }}"
                                    value="{{ old('billing_email', $organization->billing_email ?? '') }}">
                            </div>

                            <!-- VAT Number (Optional) -->
                            <div>
                                <label for="vat_number" class="block text-sm font-medium text-gray-300 mb-1">
                                    {{ __('checkout.vat_number') }} <span class="text-gray-500 text-xs">({{ __('checkout.optional') }})</span>
                                </label>
                                <input type="text" id="vat_number" name="vat_number"
                                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="{{ __('checkout.vat_number_placeholder') }}"
                                    value="{{ old('vat_number', $organization->vat_number ?? '') }}">
                            </div>

                            <!-- Address Line 1 -->
                            <div>
                                <label for="address_line1" class="block text-sm font-medium text-gray-300 mb-1">
                                    {{ __('checkout.address_line1') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="address_line1" name="address_line1" required
                                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="{{ __('checkout.address_line1_placeholder') }}"
                                    value="{{ old('address_line1', $organization->address_line1 ?? '') }}">
                            </div>

                            <!-- Address Line 2 -->
                            <div>
                                <label for="address_line2" class="block text-sm font-medium text-gray-300 mb-1">
                                    {{ __('checkout.address_line2') }} <span class="text-gray-500 text-xs">({{ __('checkout.optional') }})</span>
                                </label>
                                <input type="text" id="address_line2" name="address_line2"
                                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="{{ __('checkout.address_line2_placeholder') }}"
                                    value="{{ old('address_line2', $organization->address_line2 ?? '') }}">
                            </div>

                            <!-- City and Postcode -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-300 mb-1">
                                        {{ __('checkout.city') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="city" name="city" required
                                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="{{ __('checkout.city_placeholder') }}"
                                        value="{{ old('city', $organization->city ?? '') }}">
                                </div>

                                <div>
                                    <label for="postcode" class="block text-sm font-medium text-gray-300 mb-1">
                                        {{ __('checkout.postcode') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="postcode" name="postcode" required
                                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="{{ __('checkout.postcode_placeholder') }}"
                                        value="{{ old('postcode', $organization->postcode ?? '') }}">
                                </div>
                            </div>

                            <!-- Country -->
                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-300 mb-1">
                                    {{ __('checkout.country') }} <span class="text-red-500">*</span>
                                </label>
                                <select id="country" name="country" required
                                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">{{ __('checkout.select_country') }}</option>
                                    @php
                                        $countries = __('countries');
                                        $denmarkKey = 'DK';
                                        $denmark = [$denmarkKey => $countries[$denmarkKey]];
                                        unset($countries[$denmarkKey]);
                                        
                                        // Sort remaining countries by translated name
                                        uasort($countries, function($a, $b) {
                                            return strcasecmp($a, $b);
                                        });
                                        
                                        // Merge Denmark first, then alphabetically sorted countries
                                        $sortedCountries = $denmark + $countries;
                                    @endphp
                                    @foreach($sortedCountries as $key => $name)
                                        <option value="{{ $key }}" {{ old('country', $organization->country ?? '') == $key ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-white mb-4">{{ __('checkout.payment_information') }}</h2>
                        
                        <!-- Card Element -->
                        <div id="card-element" class="p-4 bg-gray-700 border border-gray-600 rounded-lg"></div>
                        
                        <!-- Card Errors -->
                        <div id="card-errors" class="mt-2 text-red-500 text-sm"></div>
                    </div>

                    <!-- Sales Terms Acceptance -->
                    <div class="mb-6">
                        <div class="flex items-start">
                            <input type="checkbox" id="accept_sales_terms" name="accept_sales_terms" value="1"
                                class="mt-1 h-4 w-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2">
                            <label for="accept_sales_terms" class="ml-3 text-sm text-gray-300">
                                {{ __('checkout.accept_sales_terms_label') }}
                                <a href="{{ config('legal.sales_terms_url') }}" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300 underline">
                                    {{ __('checkout.sales_terms_link_text') }}
                                </a>
                            </label>
                        </div>
                        <div id="sales-terms-error" class="mt-2 text-red-500 text-sm hidden"></div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submit-button"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 disabled:bg-gray-600 disabled:cursor-not-allowed">
                        <span id="button-text">{{ __('checkout.complete_payment') }}</span>
                        <span id="spinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('checkout.processing') }}
                        </span>
                    </button>

                    <!-- Error Messages -->
                    <div id="payment-errors" class="mt-4 p-4 bg-red-900 border border-red-700 rounded-lg text-red-200 text-sm hidden"></div>
                </form>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-lg shadow-lg p-6 sticky top-6">
                    <h2 class="text-xl font-semibold text-white mb-4">{{ __('checkout.order_summary') }}</h2>
                    
                    <!-- Billing Period Toggle -->
                    <div class="mb-4 flex items-center gap-2 bg-gray-700 rounded-lg p-1">
                        <button type="button" onclick="switchBillingPeriod('monthly')" id="btn-monthly" 
                            class="flex-1 px-3 py-2 rounded-md text-sm font-medium transition {{ $billingPeriod === 'monthly' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white' }}">
                            {{ __('subscriptions.membership.monthly') }}
                        </button>
                        <button type="button" onclick="switchBillingPeriod('yearly')" id="btn-yearly" 
                            class="flex-1 px-3 py-2 rounded-md text-sm font-medium transition {{ $billingPeriod === 'yearly' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white' }}">
                            {{ __('subscriptions.membership.yearly') }}
                            <span class="ml-1 text-xs text-green-400">-17%</span>
                        </button>
                    </div>
                    
                    <!-- Plan Details -->
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-gray-300">
                            <span>{{ __('checkout.plan') }}</span>
                            <span class="font-semibold text-white">{{ $plan->name }}</span>
                        </div>
                        
                        <div class="flex justify-between text-gray-300">
                            <span>{{ __('checkout.billing_period') }}</span>
                            <span class="font-semibold text-white" id="period-label">{{ ucfirst($billingPeriod) }}</span>
                        </div>
                        
                        <!-- Recurring Subscription Notice -->
                        <div class="bg-blue-900/30 border border-blue-500/30 rounded-lg p-3 text-sm">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <div class="text-blue-300">
                                    <div class="font-semibold mb-1">{{ __('checkout.recurring_subscription') }}</div>
                                    <div class="text-blue-400 text-xs">{{ __('checkout.recurring_description') }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-700 pt-3 mt-3">
                            <div class="flex justify-between text-gray-300 mb-2">
                                <span>{{ __('checkout.subtotal') }}</span>
                                <span id="subtotal-amount">{{ $priceFormatted }}</span>
                            </div>
                            
                            <div class="flex justify-between text-gray-300 text-sm mb-2">
                                <span>{{ __('checkout.vat_included') }} (25%)</span>
                                <span id="tax-amount">{{ number_format($priceInCents * 0.25 / 1.25 / 100, 2) }}</span>
                            </div>
                            
                            <div class="flex justify-between text-xl font-bold text-white pt-3 border-t border-gray-700">
                                <span>{{ __('checkout.total') }}</span>
                                <span id="total-amount">{{ $priceFormatted }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Security Badge -->
                    <div class="flex items-center justify-center space-x-2 text-gray-400 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span>{{ __('checkout.secure_payment') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing checkout...');
        
        // Pricing data
        const planPrices = {
            monthly: {{ $plan->monthly_price_cents }},
            yearly: {{ $plan->yearly_price_cents }}
        };
        
        let currentBillingPeriod = '{{ $billingPeriod }}';
        
        // Switch billing period function
        window.switchBillingPeriod = function(period) {
            currentBillingPeriod = period;
            
            const btnMonthly = document.getElementById('btn-monthly');
        const btnYearly = document.getElementById('btn-yearly');
        const periodLabel = document.getElementById('period-label');
        const billingPeriodInput = document.querySelector('input[name="billing_period"]');
        
        // Update button styles
        if (period === 'monthly') {
            btnMonthly.classList.add('bg-blue-600', 'text-white');
            btnMonthly.classList.remove('text-gray-400', 'hover:text-white');
            btnYearly.classList.remove('bg-blue-600', 'text-white');
            btnYearly.classList.add('text-gray-400', 'hover:text-white');
            periodLabel.textContent = 'Monthly';
        } else {
            btnYearly.classList.add('bg-blue-600', 'text-white');
            btnYearly.classList.remove('text-gray-400', 'hover:text-white');
            btnMonthly.classList.remove('bg-blue-600', 'text-white');
            btnMonthly.classList.add('text-gray-400', 'hover:text-white');
            periodLabel.textContent = 'Yearly';
        }
        
        // Update hidden input
        billingPeriodInput.value = period;
        
        // Update prices
        updatePrices(period);
        };
    
        function updatePrices(period) {
            const priceInCents = planPrices[period];
            const total = priceInCents / 100;
            const vatIncluded = total * 0.25 / 1.25; // Calculate VAT portion (20% of total when VAT is 25% included)
            
            document.getElementById('subtotal-amount').textContent = total.toFixed(2);
            document.getElementById('tax-amount').textContent = vatIncluded.toFixed(2);
            document.getElementById('total-amount').textContent = total.toFixed(2);
        }

        // Initialize Stripe
        console.log('Initializing Stripe...');
        const stripe = Stripe('{{ $stripeKey }}', {
            locale: '{{ app()->getLocale() }}'
        });
        const elements = stripe.elements();

        // Create card element
        console.log('Creating card element...');
        const cardElement = elements.create('card', {
        hidePostalCode: true,
        style: {
            base: {
                color: '#fff',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#9ca3af'
                }
            },
            invalid: {
                color: '#ef4444',
                iconColor: '#ef4444'
            }
        }
        });
        
        cardElement.mount('#card-element');
        console.log('Card element mounted');
        
        // Update card element when country changes
        const countrySelect = document.getElementById('country');
        countrySelect.addEventListener('change', function() {
            // Remount card element is not needed, postal code validation happens on submit
        });

        // Handle card validation errors
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Handle form submission
        const form = document.getElementById('checkout-form');
        const submitButton = document.getElementById('submit-button');
        const buttonText = document.getElementById('button-text');
        const spinner = document.getElementById('spinner');
        const paymentErrors = document.getElementById('payment-errors');
        const acceptSalesTermsCheckbox = document.getElementById('accept_sales_terms');
        const salesTermsError = document.getElementById('sales-terms-error');

        // Update submit button state based on checkbox
        function updateSubmitButtonState() {
            const isChecked = acceptSalesTermsCheckbox.checked;
            submitButton.disabled = !isChecked;
        }

        // Initialize button state
        updateSubmitButtonState();

        // Listen for checkbox changes
        acceptSalesTermsCheckbox.addEventListener('change', function() {
            updateSubmitButtonState();
            // Clear error when checkbox is checked
            if (this.checked) {
                salesTermsError.classList.add('hidden');
                salesTermsError.textContent = '';
            }
        });

        console.log('Form listener ready');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            console.log('Form submitted, preventing default');

            // Validate sales terms acceptance
            if (!acceptSalesTermsCheckbox.checked) {
                salesTermsError.textContent = '{{ __("validation.accepted", ["attribute" => __("checkout.accept_sales_terms_label")]) }}';
                salesTermsError.classList.remove('hidden');
                return;
            }

            // Clear sales terms error
            salesTermsError.classList.add('hidden');
            salesTermsError.textContent = '';

            // Disable submit button
            submitButton.disabled = true;
            buttonText.classList.add('hidden');
            spinner.classList.remove('hidden');
            paymentErrors.classList.add('hidden');

        try {
            // Step 1: Create Setup Intent
            console.log('Step 1: Creating setup intent for subscription...');
            const createResponse = await fetch('{{ route("checkout.createPaymentIntent") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    plan_id: document.querySelector('[name="plan_id"]').value,
                    billing_period: document.querySelector('[name="billing_period"]').value
                })
            });

            const createData = await createResponse.json();
            console.log('Create response:', createData);

            if (!createResponse.ok) {
                throw new Error(createData.error || 'Failed to create setup intent');
            }

            if (!createData.clientSecret) {
                throw new Error('No client secret returned from server');
            }

            // Step 2: Confirm Setup Intent (collect payment method)
            console.log('Step 2: Confirming payment method...');
            const {error, setupIntent} = await stripe.confirmCardSetup(
                createData.clientSecret,
                {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: document.getElementById('company').value,
                            email: document.getElementById('billing_email').value,
                            address: {
                                line1: document.getElementById('address_line1').value,
                                line2: document.getElementById('address_line2').value,
                                city: document.getElementById('city').value,
                                postal_code: document.getElementById('postcode').value,
                                country: document.getElementById('country').value
                            }
                        }
                    }
                }
            );

            console.log('Confirm result:', {error, setupIntent});

            if (error) {
                throw new Error(error.message);
            }

            if (!setupIntent || setupIntent.status !== 'succeeded') {
                throw new Error('Payment method setup was not successful');
            }

            // Step 3: Process subscription on Server
            console.log('Step 3: Creating subscription on server...');
            
            const processResponse = await fetch('{{ route("checkout.process") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    setup_intent_id: setupIntent.id,
                    plan_id: document.querySelector('[name="plan_id"]').value,
                    billing_period: document.querySelector('[name="billing_period"]').value,
                    company: document.getElementById('company').value,
                    billing_email: document.getElementById('billing_email').value,
                    vat_number: document.getElementById('vat_number').value,
                    address_line1: document.getElementById('address_line1').value,
                    address_line2: document.getElementById('address_line2').value,
                    city: document.getElementById('city').value,
                    postcode: document.getElementById('postcode').value,
                    country: document.getElementById('country').value,
                    accept_sales_terms: acceptSalesTermsCheckbox.checked ? '1' : '0'
                })
            });

            const processData = await processResponse.json();

            if (!processResponse.ok) {
                // Handle validation errors (Laravel returns 422 for validation failures)
                if (processResponse.status === 422 && processData.errors) {
                    if (processData.errors.accept_sales_terms) {
                        salesTermsError.textContent = processData.errors.accept_sales_terms[0];
                        salesTermsError.classList.remove('hidden');
                    }
                    // Show general validation error if no specific field error
                    const errorMessage = processData.message || 'Validation failed. Please check the form.';
                    throw new Error(errorMessage);
                }
                throw new Error(processData.error || processData.message || 'Failed to process payment');
            }

            // Redirect to success page
            window.location.href = '{{ route("checkout.success") }}?invoice_id=' + processData.invoice_id;

        } catch (error) {
            // Show error message
            paymentErrors.textContent = error.message;
            paymentErrors.classList.remove('hidden');

            // Re-enable submit button (only if checkbox is checked)
            updateSubmitButtonState();
            buttonText.classList.remove('hidden');
            spinner.classList.add('hidden');
        }
    });
    
    }); // End DOMContentLoaded
</script>
@endsection
