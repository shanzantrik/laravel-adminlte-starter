<div class="row mb-3">
    <div class="col-md-12">
        <div class="float-right">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.booking-advances.index') }}" class="text-decoration-none">
                        <i class="fas fa-list"></i> View All Booking Advances
                    </a>
                </li>
            </ol>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Customer and Booking Details -->

        <div class="form-group">
            <label for="customer_search">{{ __('Search Customer by Phone Number or Vehicle Registration*') }}</label>
            <div class="input-group">
                <input type="text" id="customer_search" class="form-control"
                    placeholder="Enter phone number or vehicle registration" autocomplete="off">
                <div class="input-group-append">
                    <button type="button" id="customer_search_button" class="btn btn-primary">Search</button>
                    <a href="{{ url('/admin/customers/create') }}" class="btn btn-success ml-2">Add New Customer</a>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="customer_id">{{ __('Select Customer') }}</label>
            <select name="customer_id" id="customer_id" class="form-control @error('customer_id') is-invalid @enderror"
                onchange="handleCustomerSelection(this.value)" {{ isset($bookingAdvance) ? 'disabled' : '' }}>
                <option value="">{{ __('Select Customer') }}</option>
                <!-- Options will be loaded dynamically based on search -->
            </select>
            @error('customer_id')
            <small class="error invalid-feedback" role="alert">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="customer_name">{{ __('Customer Name') }}</label>
            <input type="text" id="customer_name" class="form-control" readonly
                value="{{ old('customer_name', isset($bookingAdvance->customer) ? $bookingAdvance->customer->name : '') }}">
        </div>


        <div class="form-group">
            <label for="order_booking_number">{{ __('Order Booking Number*') }}</label>
            <input type="text" name="order_booking_number"
                class="form-control @error('order_booking_number') is-invalid @enderror" id="order_booking_number"
                placeholder="Enter booking number"
                value="{{ old('order_booking_number', $bookingAdvance->order_booking_number ?? '') }}" required>
            @error('order_booking_number')
            <small class="error invalid-feedback" role="alert">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="total_amount">{{ __('Total Amount*') }}</label>
            <input type="number" step="0.01" name="total_amount"
                class="form-control @error('total_amount') is-invalid @enderror" id="total_amount"
                placeholder="Enter total amount" value="{{ old('total_amount', $bookingAdvance->total_amount ?? '') }}"
                required>
            @error('total_amount')
            <small class="error invalid-feedback" role="alert">{{ $message }}</small>
            @enderror
        </div>

        <!-- Payment Method Entries -->
        <div id="paymentEntries">
            <h5>Payments</h5>
            <button type="button" class="btn btn-secondary mb-2" onclick="addPaymentRow()">Add Payment</button>
            <div id="paymentsContainer">
                <!-- Existing payments will be populated here dynamically for update -->
            </div>
        </div>

        <!-- Form Buttons -->
        <div class="form-group d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-primary">
                {{ isset($bookingAdvance) ? 'Update' : 'Save' }}
            </button>
            <a href="{{ route('admin.booking-advances.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>

    <!-- Payment Summary Column -->
    <div class="col-md-4">
        <h4>Complete Payment Details</h4>
        <div id="paymentDetails">
            <p>Total Amount: ₹<span id="totalAmountDisplay">0.00</span></p>
            <p>Total Amount Paid: ₹<span id="amountPaidDisplay">0.00</span></p>
            <p>Balance: ₹<span id="balanceDisplay">0.00</span></p>
            <h4>Individual Payments Details</h4>
            <div id="individualPayments">
                <!-- Individual payment methods and amounts will be displayed here dynamically -->
            </div>
        </div>
        <div class="text-left">
            @if(isset($bookingAdvance) && $bookingAdvance->id)
            <a href="{{ route('admin.booking-advances.receipt', $bookingAdvance) }}" class="btn btn-info"
                target="_blank">
                <i class="fas fa-print"></i> Generate Receipt
            </a>
            @endif
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Define updatePaymentSummary first
        window.updatePaymentSummary = function() {
            const totalAmount = parseFloat(document.getElementById("total_amount").value) || 0;
            const paymentAmounts = document.querySelectorAll("[name^='payments'][name$='[amount]']");
            let amountPaid = 0;
            const paymentDetailsContainer = document.getElementById("individualPayments");
            paymentDetailsContainer.innerHTML = ""; // Clear current displayed payments

            paymentAmounts.forEach(input => {
                const amount = parseFloat(input.value) || 0;
                amountPaid += amount;

                const paymentRow = document.createElement("p");
                const paymentType = input.closest(".row").querySelector("select").value;
                paymentRow.innerText = `${paymentType.toUpperCase()}: ₹${amount.toFixed(2)}`;
                paymentDetailsContainer.appendChild(paymentRow);
            });

            const balance = totalAmount - amountPaid;

            document.getElementById("totalAmountDisplay").innerText = totalAmount.toFixed(2);
            document.getElementById("amountPaidDisplay").innerText = amountPaid.toFixed(2);
            document.getElementById("balanceDisplay").innerText = balance.toFixed(2);
        };

        // Add event listener for total amount
        document.getElementById("total_amount").addEventListener("input", updatePaymentSummary);

        // Ensure the `addPaymentRow` function is globally available
        window.addPaymentRow = function (payment = {}) {
            const container = document.getElementById('paymentsContainer');
            const index = container.children.length;

            // Get all currently selected payment methods
            const selectedMethods = Array.from(document.querySelectorAll('.payment-type'))
                .map(select => select.value)
                .filter(value => value !== '');

            const paymentRow = document.createElement('div');
            paymentRow.className = 'form-group row payment-row';
            paymentRow.innerHTML = `
                <div class="col-md-3">
                    <select name="payments[${index}][payment_by]" class="form-control payment-type"
                        onchange="showPaymentFields(this, ${index})" required>
                        <option value="">Select Payment Method</option>
                        ${getAvailablePaymentOptions(selectedMethods, payment.payment_by)}
                    </select>
                </div>
                <div id="paymentFields${index}" class="col-md-8"></div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm" onclick="deletePaymentRow(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(paymentRow);

            // Show specific fields based on payment method if editing
            if (payment.payment_by) {
                window.showPaymentFields(paymentRow.querySelector(".payment-type"), index, payment);
            }
        };

        // Function to get available payment options
        function getAvailablePaymentOptions(selectedMethods, currentValue = '') {
            const allPaymentMethods = {
                cash: 'Cash',
                cheque: 'Cheque',
                bank_transfer: 'Bank Transfer',
                card: 'Card Swipe',
                advance_adjustment: 'Advance Adjustment',
                discount: 'Discount',
                credit_individual: 'Credit Individual',
                credit_institutional: 'Credit Institutional',
                balance: 'Balance'
            };

            return Object.entries(allPaymentMethods)
                .map(([value, label]) => {
                    const isSelected = value === currentValue;
                    const isDisabled = !isSelected && selectedMethods.includes(value);
                    return `<option value="${value}" ${isSelected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}>${label}</option>`;
                })
                .join('');
        }

        // Function to delete payment row
        window.deletePaymentRow = function(button) {
            const row = button.closest('.payment-row');
            row.remove();
            updatePaymentSummary();
        };

        // Ensure the `showPaymentFields` function is globally available
        window.showPaymentFields = function (select, index, payment = {}) {
            const paymentType = select.value;
            const paymentFields = document.getElementById(`paymentFields${index}`);
            paymentFields.innerHTML = ''; // Clear existing fields

            const currentDate = new Date().toISOString().split('T')[0];
            const commonFields = `
                <input type="hidden" name="payments[${index}][payment_date]"
                    value="${payment.payment_date || currentDate}">
                <input type="number" step="0.01" name="payments[${index}][amount]" placeholder="Amount"
                    class="form-control mt-2" value="${payment.amount || ''}" oninput="updatePaymentSummary()" required>
            `;

            if (paymentType === "cash") {
                paymentFields.innerHTML += commonFields;
            } else if (paymentType === "cheque") {
                paymentFields.innerHTML += `
                    <input type="text" name="payments[${index}][reference_number]" placeholder="Cheque Number"
                        class="form-control mt-2" value="${payment.reference_number || ''}" required>
                    <input type="text" name="payments[${index}][bank_name]" placeholder="Bank Name"
                        class="form-control mt-2" value="${payment.bank_name || ''}" required>
                    ${commonFields}
                `;
            } else if (paymentType === "bank_transfer") {
                paymentFields.innerHTML += `
                    <input type="text" name="payments[${index}][reference_number]" placeholder="NEFT/IFSC REF No"
                        class="form-control mt-2" value="${payment.reference_number || ''}" required>
                    <input type="text" name="payments[${index}][bank_name]" placeholder="Bank Name"
                        class="form-control mt-2" value="${payment.bank_name || ''}" required>
                    ${commonFields}
                `;
            } else if (paymentType === "card") {
                paymentFields.innerHTML += `
                    <input type="text" name="payments[${index}][reference_number]" placeholder="Card Transaction ID"
                        class="form-control mt-2" value="${payment.reference_number || ''}" required>
                    ${commonFields}
                `;
            } else if (paymentType === "advance_adjustment") {
                paymentFields.innerHTML += `
                    <input type="text" name="payments[${index}][reference_number]" placeholder="GAPL M.R.No."
                        class="form-control mt-2" value="${payment.reference_number || ''}" required>
                    ${commonFields}
                `;
            } else if (paymentType === "discount") {
                paymentFields.innerHTML += `
                    <input type="text" name="payments[${index}][approved_by]" placeholder="Approved By"
                        class="form-control mt-2" value="${payment.approved_by || ''}" required>
                    <input type="text" name="payments[${index}][discount_note_no]" placeholder="Discount Note No."
                        class="form-control mt-2" value="${payment.discount_note_no || ''}" required>
                    ${commonFields}
                `;
            } else if (paymentType === "credit_individual") {
                paymentFields.innerHTML += `
                    <input type="text" name="payments[${index}][approved_by]" placeholder="Approved By"
                        class="form-control mt-2" value="${payment.approved_by || ''}" required>
                    <input type="text" name="payments[${index}][approved_note_no]" placeholder="Approved Note No."
                        class="form-control mt-2" value="${payment.approved_note_no || ''}" required>
                    ${commonFields}
                `;
            } else if (paymentType === "credit_institutional") {
                paymentFields.innerHTML += `
                    <input type="text" name="payments[${index}][approved_by]" placeholder="Approved By"
                        class="form-control mt-2" value="${payment.approved_by || ''}" required>
                    <input type="text" name="payments[${index}][institution_name]" placeholder="Institution Name"
                        class="form-control mt-2" value="${payment.institution_name || ''}" required>
                    <input type="text" name="payments[${index}][credit_instrument]" placeholder="Credit Instrument"
                        class="form-control mt-2" value="${payment.credit_instrument || ''}" required>
                    <input type="text" name="payments[${index}][reference_number]" placeholder="Credit Instrument Reference No."
                        class="form-control mt-2" value="${payment.reference_number || ''}" required>
                    ${commonFields}
                `;
            } else if (paymentType === "balance") {
                paymentFields.innerHTML += commonFields;
            }

            updatePaymentSummary();
        };

        // Prepopulate payments if in edit mode
        @if (isset($bookingAdvance) && $bookingAdvance->payments)
            @foreach ($bookingAdvance->payments as $payment)
                addPaymentRow({
                    payment_by: @json($payment->payment_by),
                    payment_date: @json($payment->payment_date),
                    amount: @json($payment->amount),
                    reference_number: @json($payment->reference_number),
                    bank_name: @json($payment->bank_name),
                    approved_by: @json($payment->approved_by),
                    discount_note_no: @json($payment->discount_note_no),
                    approved_note_no: @json($payment->approved_note_no),
                    institution_name: @json($payment->institution_name),
                    credit_instrument: @json($payment->credit_instrument)
                });
            @endforeach
        @endif

        // Cache DOM elements
        const elements = {
            searchInput: document.getElementById("customer_search"),
            searchButton: document.getElementById("customer_search_button"),
            customerSelect: document.getElementById("customer_id"),
            customerNameInput: document.getElementById("customer_name")
        };
        let customersData = [];
        // Configuration
        const config = {
            minSearchLength: 3,
            searchEndpoint: '/admin/customers/search', // Verify this matches your Laravel route
            debounceDelay: 300
        };

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Debug: Log CSRF token presence
        console.log('CSRF Token present:', !!csrfToken);

        // Validate if all required elements are present
        if (!validateElements(elements)) {
            console.error("Missing DOM elements:",
                Object.entries(elements)
                    .filter(([key, value]) => !value)
                    .map(([key]) => key)
            );
            return;
        }

        function validateElements(elements) {
            return Object.values(elements).every(element => element !== null);
        }

        // Initialize event listeners
        elements.searchButton.addEventListener("click", () => {
            console.log("Search button clicked");
            handleSearch();
        });

        elements.searchInput.addEventListener("input", debounce(function(e) {
            console.log("Input changed:", e.target.value);
            if (e.target.value.length >= config.minSearchLength) {
                handleSearch();
            }
        }, config.debounceDelay));

        async function handleSearch() {
            const query = elements.searchInput.value.trim();
            console.log('Handling search for query:', query);

            if (query.length < 3) {
                alert("Please enter at least 3 characters to search.");
                return;
            }

            try {
                showMessage("Searching...", "info");
                const data = await fetchCustomers(query);
                customersData = data;
                console.log('Received data:', data);
                updateCustomerDropdown(data);
            } catch (error) {
                console.error("Detailed error:", error);
                showMessage(`Error: ${error.message}`, "error");
            }
        }

        async function fetchCustomers(query) {
            const searchUrl = `${config.searchEndpoint}?query=${encodeURIComponent(query)}`;
            console.log('Fetching from URL:', searchUrl);

            try {
                const response = await fetch(searchUrl, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest' // Add this for Laravel to detect AJAX request
                    },
                    credentials: 'same-origin'
                });

                console.log('Response status:', response.status);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Response error text:', errorText);
                    throw new Error(`Server responded with status: ${response.status}`);
                }

                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("Received non-JSON response from server");
                }

                return await response.json();
            } catch (error) {
                console.error('Fetch error:', error);
                throw error;
            }
        }

        function updateCustomerDropdown(customers) {
            console.log('Updating dropdown with customers:', customers);

            // Clear existing options
            elements.customerSelect.innerHTML = '<option value="">Select Customer</option>';

            if (!Array.isArray(customers)) {
                console.error('Received non-array customers data:', customers);
                showMessage("Invalid data format received from server.", "error");
                return;
            }

            if (customers.length === 0) {
                showMessage("No customers found.", "info");
                return;
            }

            // Add new options
            customers.forEach(customer => {
                const option = document.createElement("option");
                option.value = customer.id;
                option.textContent = formatCustomerOption(customer);
                option.dataset.name = customer.name;
                elements.customerSelect.appendChild(option);
            });

            showMessage(`Found ${customers.length} customers`, "success");
        }

        // Make handleCustomerSelection globally available
        window.handleCustomerSelection = function(selectedId) {
            console.log('Selected customer ID:', selectedId);

            if (!selectedId) {
                document.getElementById('customer_name').value = '';
                return;
            }

            // Find the selected customer from stored data
            const selectedCustomer = customersData.find(customer => customer.id.toString() === selectedId.toString());

            if (selectedCustomer) {
                console.log('Found customer:', selectedCustomer);
                document.getElementById('customer_name').value = selectedCustomer.name || '';
            } else {
                console.error('Customer not found for ID:', selectedId);
                document.getElementById('customer_name').value = '';
            }
        };

        function formatCustomerOption(customer) {
            return `${customer.name || 'N/A'} - ${customer.phone_no || 'N/A'} - ${customer.vehicle_registration_no || 'N/A'}`;
        }

        function showMessage(message, type = 'info') {
            console.log(`${type}: ${message}`);

            // You can replace this with a more sophisticated notification system
            if (type === 'error') {
                alert(message);
            }
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Additional debug info on load
        console.log('Search module initialized');
        console.log('Current endpoint:', config.searchEndpoint);

        // Initialize customer data if in edit mode
        @if(isset($bookingAdvance) && $bookingAdvance->customer)
            customersData = [@json($bookingAdvance->customer)];
        @endif
    });



</script>
