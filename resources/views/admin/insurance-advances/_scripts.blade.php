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
    let selectedIndex = -1;
    let currentResults = [];

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

    // Function to display results
    function displayResults(results) {
        currentResults = results;
        customerResults.innerHTML = results.map((customer, index) => `
            <li class="list-group-item" data-index="${index}">
                ${customer.name} - ${customer.phone_no}
            </li>
        `).join('');
        customerResults.style.display = "block";

        // Add click event listeners to results
        customerResults.querySelectorAll('.list-group-item').forEach(item => {
            item.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                if (!isNaN(index) && currentResults[index]) {
                    selectCustomer(currentResults[index]);
                }
            });
        });
    }

    // Function to select a customer
    function selectCustomer(customer) {
        customerIdInput.value = customer.id;
        customerNameInput.value = customer.name;
        customerPhoneInput.value = customer.phone_no;
        customerPanInput.value = customer.pan_number || '';
        customerResults.style.display = "none";
        searchInput.value = customer.phone_no;
        disableManualInput();
    }

    // Function to enable manual input
    function enableManualInput() {
        customerNameInput.readOnly = false;
        customerPanInput.readOnly = false;
        customerIdInput.value = '';
    }

    // Function to disable manual input
    function disableManualInput() {
        customerNameInput.readOnly = true;
        customerPanInput.readOnly = true;
    }

    // Add payment row function
    window.addPaymentRow = function(existingPayment = null) {
        const container = document.createElement('div');
        container.className = 'payment-row border rounded p-3 mb-3 position-relative';

        const selectedMethods = Array.from(document.querySelectorAll('.payment-type'))
            .map(select => select.value);

        const commonFields = `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Payment Method*</label>
                        <select class="form-control payment-type" name="payments[][payment_by]" required onchange="showPaymentFields(this, ${document.querySelectorAll('.payment-row').length})">
                            ${getPaymentMethodOptions(selectedMethods, existingPayment?.payment_by)}
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Amount*</label>
                        <input type="number" step="0.01" class="form-control payment-amount" name="payments[][amount]" required value="${existingPayment?.amount || ''}" onchange="updatePaymentSummary()">
                    </div>
                </div>
            </div>
            <div class="payment-fields"></div>
        `;

        container.innerHTML = commonFields + `
            <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 10px; right: 10px;" onclick="removePaymentRow(this)">
                <i class="fas fa-times"></i>
            </button>
        `;

        document.getElementById('paymentsContainer').appendChild(container);

        if (existingPayment) {
            const select = container.querySelector('.payment-type');
            showPaymentFields(select, document.querySelectorAll('.payment-row').length - 1, existingPayment);
        }

        updatePaymentSummary();
    };

    // Remove payment row function
    window.removePaymentRow = function(button) {
        button.closest('.payment-row').remove();
        updatePaymentSummary();
    };

    // Show payment fields based on payment method
    window.showPaymentFields = function(select, index, existingPayment = null) {
        const container = select.closest('.payment-row').querySelector('.payment-fields');
        const paymentMethod = select.value;
        let fields = '';

        const commonFields = `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Payment Date*</label>
                        <input type="date" class="form-control" name="payments[][payment_date]" required value="${existingPayment?.payment_date || ''}">
                    </div>
                </div>
            </div>
        `;

        switch (paymentMethod) {
            case 'cheque':
            case 'dd':
                fields = commonFields + `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Reference Number*</label>
                                <input type="text" class="form-control" name="payments[][reference_number]" required value="${existingPayment?.reference_number || ''}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bank Name*</label>
                                <input type="text" class="form-control" name="payments[][bank_name]" required value="${existingPayment?.bank_name || ''}">
                            </div>
                        </div>
                    </div>
                `;
                break;

            case 'credit_note':
                fields = commonFields + `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Approved By*</label>
                                <input type="text" class="form-control" name="payments[][approved_by]" required value="${existingPayment?.approved_by || ''}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Discount Note No*</label>
                                <input type="text" class="form-control" name="payments[][discount_note_no]" required value="${existingPayment?.discount_note_no || ''}">
                            </div>
                        </div>
                    </div>
                `;
                break;

            case 'finance':
                fields = commonFields + `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Institution Name*</label>
                                <input type="text" class="form-control" name="payments[][institution_name]" required value="${existingPayment?.institution_name || ''}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Credit Instrument*</label>
                                <input type="text" class="form-control" name="payments[][credit_instrument]" required value="${existingPayment?.credit_instrument || ''}">
                            </div>
                        </div>
                    </div>
                `;
                break;

            default:
                fields = commonFields;
        }

        container.innerHTML = fields;
        updatePaymentSummary();
    };

    window.updatePaymentSummary = function() {
        const totalAmount = parseFloat(document.getElementById('total_amount').value) || 0;
        let totalPaid = 0;
        const payments = {};

        document.querySelectorAll('.payment-amount').forEach(input => {
            const amount = parseFloat(input.value) || 0;
            totalPaid += amount;

            const paymentType = input.closest('.payment-row').querySelector('.payment-type').value;
            payments[paymentType] = (payments[paymentType] || 0) + amount;
        });

        document.getElementById('totalAmountDisplay').textContent = totalAmount.toFixed(2);
        document.getElementById('amountPaidDisplay').textContent = totalPaid.toFixed(2);
        document.getElementById('balanceDisplay').textContent = (totalAmount - totalPaid).toFixed(2);

        // Update individual payments display
        const individualPayments = document.getElementById('individualPayments');
        individualPayments.innerHTML = Object.entries(payments)
            .map(([type, amount]) => `
                <p>${type.replace('_', ' ').toUpperCase()}: ₹${amount.toFixed(2)}</p>
            `).join('');
    };

    function getPaymentMethodOptions(selectedMethods, currentValue = null) {
        const allPaymentMethods = {
            cash: 'Cash',
            cheque: 'Cheque',
            dd: 'DD',
            upi: 'UPI',
            credit_note: 'Credit Note',
            finance: 'Finance'
        };

        return Object.entries(allPaymentMethods)
            .map(([value, label]) => {
                const isSelected = value === currentValue;
                const isDisabled = !isSelected && selectedMethods.includes(value);
                return `<option value="${value}" ${isSelected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}>${label}</option>`;
            })
            .join('');
    }

    // Initialize form
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

    // Form validation
    document.getElementById('sale-form').addEventListener('submit', function(e) {
        const totalAmount = parseFloat(document.getElementById('total_amount').value) || 0;
        const amountPaid = parseFloat(document.getElementById('amountPaidDisplay').innerText) || 0;

        if (amountPaid > totalAmount) {
            e.preventDefault();
            alert('Total paid amount cannot exceed the total amount');
            return false;
        }

        if (document.getElementById('paymentsContainer').children.length === 0) {
            e.preventDefault();
            alert('Please add at least one payment');
            return false;
        }

        return true;
    });

    // Initialize existing payments if in edit mode
    @if(isset($insuranceAdvance) && $insuranceAdvance->payments)
        @foreach($insuranceAdvance->payments as $payment)
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
});
</script>
