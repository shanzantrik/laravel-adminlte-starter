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

        <div class="form-group position-relative">
            <label for="customer_search">Phone Number</label>
            <input type="text" id="customer_search" class="form-control"
                placeholder="Search customer by phone number by entering first 3 digits or more">
            <input type="hidden" id="customer_id" name="customer_id">
            <!-- Results List -->
            <ul id="customer_results" class="list-group position-absolute w-100" style="z-index: 1000; display: none;">
            </ul>
        </div>

        <div class="form-group">
            <label for="customer_name">Customer Name</label>
            <input type="text" id="customer_name" name="customer_name" class="form-control" readonly
                value="{{ old('customer_name', isset($bookingAdvance->customer) ? $bookingAdvance->customer->name : '') }}">
        </div>

        <div class="form-group">
            <label for="customer_pan_no">PAN Number</label>
            <input type="text" id="customer_pan_no" name="customer_pan_no" class="form-control" readonly
                value="{{ old('customer_pan_no', isset($bookingAdvance->customer) ? $bookingAdvance->customer->phone_no : '') }}">
        </div>

        <div class="form-group">
            <input type="hidden" id="customer_phone_no" name="customer_phone_no" class="form-control" readonly
                value="{{ old('customer_phone_no', isset($bookingAdvance->customer) ? $bookingAdvance->customer->phone_no : '') }}">
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
            <label for="sales_exec_name">{{ __('Sales Executive Name') }}</label>
            <input type="text" name="sales_exec_name"
                class="form-control @error('sales_exec_name') is-invalid @enderror" id="sales_exec_name"
                placeholder="Enter Sales Executive Name"
                value="{{ old('sales_exec_name', $bookingAdvance->sales_exec_name ?? '') }}" required>
            @error('sales_exec_name')
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

            <div id="paymentsContainer">
                <!-- Existing payments will be populated here dynamically for update -->
            </div>
            <button type="button" class="btn btn-secondary mb-2" onclick="addPaymentRow()">Add Payment</button>
        </div>

        <!-- Form Buttons -->
        <div class="form-group d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-primary" id="saveButton" disabled>
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
<style>
    #customer_results {
        max-height: 200px;
        overflow-y: auto;
        width: 100%;
    }

    #customer_results .list-group-item {
        padding: 10px;
        font-size: 14px;
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("customer_search");
    const customerResults = document.getElementById("customer_results");
    const customerNameInput = document.getElementById("customer_name");
    const customerPhoneInput = document.getElementById("customer_phone_no");
    const customerPanInput = document.getElementById("customer_pan_no");
    const customerIdInput = document.getElementById("customer_id");

    let debounceTimer;

    // Event listener for input field
    searchInput.addEventListener("input", function () {
        clearTimeout(debounceTimer);
        const query = searchInput.value.trim();

        if (query.length >= 3) {
            debounceTimer = setTimeout(() => {
                fetchCustomers(query);
            }, 300);
        } else {
            customerResults.style.display = "none";
        }
    });

    // Function to fetch customers via AJAX
    function fetchCustomers(query) {
        fetch(`/admin/customers/search?query=${encodeURIComponent(query)}`, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                // If no customers found, enable manual input
                enableManualInput();
                customerResults.innerHTML = `
                    <li class="list-group-item text-muted">
                        No customers found. You can enter details for a new customer below.
                    </li>`;
                customerResults.style.display = "block";
            } else {
                displayResults(data);
            }
        })
        .catch(error => console.error("Error fetching customers:", error));
    }

    function enableManualInput() {
        // Clear customer ID as this will be a new customer
        customerIdInput.value = '';

        // Enable name input for new customer
        customerNameInput.removeAttribute('readonly');
        customerPhoneInput.removeAttribute('readonly');
        customerPanInput.removeAttribute('readonly');
        customerNameInput.value = '';
        // Set values from search if they match a phone pattern
        const searchValue = searchInput.value.trim();
        if (/^\d{10}$/.test(searchValue)) {
            customerPhoneInput.value = searchValue;
        } else {
            customerPhoneInput.value = searchValue;
        }
    }

    // Function to display autocomplete results
    function displayResults(customers) {
        customerResults.innerHTML = ""; // Clear existing results
        if (customers.length === 0) {
            customerResults.innerHTML = `<li class="list-group-item text-muted">No customers found</li>`;
        } else {
        customers.forEach(customer => {
            // Main customer row
            const customerRow = document.createElement("li");
            customerRow.className = "list-group-item list-group-item-action customer-row";

            // Customer info
            const customerInfo = document.createElement("div");
            customerInfo.className = "customer-info";
            customerInfo.innerHTML = `
                <strong>${customer.name}</strong><br>
                <small>Phone: ${customer.phone_no} | PAN: ${customer.pan_number}</small>
            `;
            customerRow.appendChild(customerInfo);

            // // Add booking numbers if they exist
            // if (customer.booking_numbers && customer.booking_numbers.length > 0) {
            //     const bookingsContainer = document.createElement("div");
            //     bookingsContainer.className = "booking-numbers mt-2";

            //     customer.booking_numbers.forEach(booking => {
            //         const bookingRow = document.createElement("div");
            //         bookingRow.className = "booking-row";
            //         // bookingRow.innerHTML = `
            //         //     <i class="fas fa-file-invoice me-2"></i>
            //         //     Booking: ${booking.number} | Amount: ₹${booking.amount.toLocaleString()} | Date: ${booking.date}
            //         // `;
            //         bookingRow.addEventListener("click", (e) => {
            //             e.stopPropagation(); // Prevent triggering parent click
            //             selectCustomerWithBooking(customer, booking.number);
            //         });
            //         bookingsContainer.appendChild(bookingRow);
            //     });

            //     customerRow.appendChild(bookingsContainer);
            // }

            // Add click event for selecting just the customer
            customerInfo.addEventListener("click", () => selectCustomer(customer));

            customerResults.appendChild(customerRow);
        });
    }
    customerResults.style.display = "block";
}

// Function to handle customer selection with booking
function selectCustomerWithBooking(customer, bookingNumber) {
    // Set customer details
    searchInput.value = `${customer.phone_no}`;
    customerIdInput.value = customer.id;
    customerNameInput.value = customer.name;
    customerPhoneInput.value = customer.phone_no;
    customerPanInput.value = customer.pan_number;
    // Set booking number
    const bookingNumberInput = document.getElementById('order_booking_number');
    if (bookingNumberInput) {
        bookingNumberInput.value = bookingNumber;
    }

    customerResults.style.display = "none";
}

// Function to handle customer selection without booking
function selectCustomer(customer) {
    searchInput.value = `${customer.phone_no}`;
    customerIdInput.value = customer.id;
    customerNameInput.value = customer.name;
    customerPhoneInput.value = customer.phone_no;
    customerPanInput.value = customer.pan_number;
    // Clear booking number if it exists
    const bookingNumberInput = document.getElementById('order_booking_number');
    if (bookingNumberInput) {
        bookingNumberInput.value = '';
    }

    customerResults.style.display = "none";
}

// Update the styles
const style = document.createElement('style');
style.textContent = `
    #customer_results {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .customer-row {
        padding: 10px;
        border-bottom: 1px solid #dee2e6;
    }

    .customer-info {
        cursor: pointer;
        padding: 5px;
    }

    .customer-info:hover {
        background-color: #f8f9fa;
    }

    .booking-numbers {
        border-top: 1px dashed #dee2e6;
        margin-top: 5px;
        padding-top: 5px;
    }

    .booking-row {
        padding: 5px 10px;
        margin: 2px 0;
        cursor: pointer;
        color: #0056b3;
        font-size: 0.9em;
        border-radius: 3px;
    }

    .booking-row:hover {
        background-color: #e9ecef;
    }

    .booking-row i {
        color: #6c757d;
    }
`;
document.head.appendChild(style);
    // Hide results on click outside
    document.addEventListener("click", function (e) {
        if (!customerResults.contains(e.target) && e.target !== searchInput) {
            customerResults.style.display = "none";
        }
    });
});
</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {

    function validateSubmitButton() {
        const balanceDisplay = parseFloat(document.getElementById("balanceDisplay").innerText) || 0;
        const isValid = balanceDisplay === 0;
        document.getElementById("saveButton").disabled = !isValid;
        document.getElementById("saveGenerateButton").disabled = !isValid;
    }
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
validateSubmitButton();
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


        // Initialize customer data if in edit mode
        @if(isset($bookingAdvance) && $bookingAdvance->customer)
            customersData = [@json($bookingAdvance->customer)];
        @endif
        // Add default cash payment row
        if (document.getElementById('paymentsContainer').children.length === 0) {
                addPaymentRow();
                setTimeout(() => {
                    const firstPaymentSelect = document.querySelector('.payment-type');
                    if (firstPaymentSelect) {
                        firstPaymentSelect.value = 'cash';
                        showPaymentFields(firstPaymentSelect, 0);
                    }
                }, 0);
            }
    });
</script>
