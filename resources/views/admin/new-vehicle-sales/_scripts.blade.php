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

    function enableManualInput() {
        customerIdInput.value = '';
        customerNameInput.removeAttribute('readonly');
        customerPhoneInput.removeAttribute('readonly');
        customerPanInput.removeAttribute('readonly');
        customerNameInput.value = '';
        const searchValue = searchInput.value.trim();
        if (/^\d{10}$/.test(searchValue)) {
            customerPhoneInput.value = searchValue;
        } else {
            customerPhoneInput.value = searchValue;
        }
    }

    function displayResults(customers) {
        currentResults = customers;
        selectedIndex = -1;
        customerResults.innerHTML = "";

        customers.forEach((customer, index) => {
            const customerRow = document.createElement("li");
            customerRow.className = "list-group-item list-group-item-action customer-row";
            customerRow.innerHTML = `
                <div class="customer-info">
                    <strong>${customer.name}</strong><br>
                    <small>Phone: ${customer.phone_no} | PAN: ${customer.pan_number}</small>
                </div>
            `;

            customerRow.addEventListener("click", () => selectCustomer(customer));
            customerRow.addEventListener("mouseover", () => {
                selectedIndex = index;
                updateSelection();
            });

            customerResults.appendChild(customerRow);
        });
        customerResults.style.display = "block";
    }

    function selectCustomer(customer) {
        searchInput.value = customer.phone_no;
        customerIdInput.value = customer.id;
        customerNameInput.value = customer.name;
        customerPhoneInput.value = customer.phone_no;
        customerPanInput.value = customer.pan_number;
        customerResults.style.display = "none";
    }

    function updateSelection() {
        const items = customerResults.getElementsByClassName('customer-row');
        Array.from(items).forEach((item, index) => {
            if (index === selectedIndex) {
                item.classList.add('active');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active');
            }
        });
    }

    // Payment handling functions
    window.addPaymentRow = function(payment = {}) {
        const container = document.getElementById('paymentsContainer');
        const index = container.children.length;

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

        if (payment.payment_by) {
            showPaymentFields(paymentRow.querySelector(".payment-type"), index, payment);
        }
    };

    window.showPaymentFields = function(select, index, payment = {}) {
        const paymentType = select.value;
        const paymentFields = document.getElementById(`paymentFields${index}`);
        paymentFields.innerHTML = '';

        const currentDate = new Date().toISOString().split('T')[0];
        const commonFields = `
            <input type="hidden" name="payments[${index}][payment_date]"
                value="${payment.payment_date || currentDate}">
            <input type="number" step="0.01" name="payments[${index}][amount]" placeholder="Amount"
                class="form-control mt-2" value="${payment.amount || ''}" required>
        `;

        switch(paymentType) {
            case 'cheque':
                paymentFields.innerHTML = `
                    <input type="text" name="payments[${index}][reference_number]" placeholder="Cheque Number"
                        class="form-control mt-2" value="${payment.reference_number || ''}" required>
                    <input type="text" name="payments[${index}][bank_name]" placeholder="Bank Name"
                        class="form-control mt-2" value="${payment.bank_name || ''}" required>
                    ${commonFields}
                `;
                break;
            case 'bank_transfer':
                paymentFields.innerHTML = `
                    <input type="text" name="payments[${index}][reference_number]" placeholder="NEFT/IFSC REF No"
                        class="form-control mt-2" value="${payment.reference_number || ''}" required>
                    <input type="text" name="payments[${index}][bank_name]" placeholder="Bank Name"
                        class="form-control mt-2" value="${payment.bank_name || ''}" required>
                    ${commonFields}
                `;
                break;
            case 'card':
                paymentFields.innerHTML = `
                    <input type="text" name="payments[${index}][reference_number]" placeholder="Card Transaction ID"
                        class="form-control mt-2" value="${payment.reference_number || ''}" required>
                    ${commonFields}
                `;
                break;
            case 'advance_adjustment':
                paymentFields.innerHTML = `
                    <input type="text" name="payments[${index}][reference_number]" placeholder="GAPL M.R.No."
                        class="form-control mt-2" value="${payment.reference_number || ''}" required>
                    ${commonFields}
                `;
                break;
            case 'discount':
                paymentFields.innerHTML = `
                    <input type="text" name="payments[${index}][approved_by]" placeholder="Approved By"
                        class="form-control mt-2" value="${payment.approved_by || ''}" required>
                    <input type="text" name="payments[${index}][discount_note_no]" placeholder="Discount Note No."
                        class="form-control mt-2" value="${payment.discount_note_no || ''}" required>
                    ${commonFields}
                `;
                break;
            case 'credit_individual':
                paymentFields.innerHTML = `
                    <input type="text" name="payments[${index}][approved_by]" placeholder="Approved By"
                        class="form-control mt-2" value="${payment.approved_by || ''}" required>
                    <input type="text" name="payments[${index}][approved_note_no]" placeholder="Approved Note No."
                        class="form-control mt-2" value="${payment.approved_note_no || ''}" required>
                    ${commonFields}
                `;
                break;
            case 'credit_institutional':
                paymentFields.innerHTML = `
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
                break;
            default:
                paymentFields.innerHTML = commonFields;
        }
        updatePaymentSummary();
    };

    window.deletePaymentRow = function(button) {
        button.closest('.payment-row').remove();
        updatePaymentSummary();
    };

    window.updatePaymentSummary = function() {
        const totalAmount = parseFloat(document.getElementById('total_amount').value) || 0;
        const paymentAmounts = document.querySelectorAll('input[name$="[amount]"]');
        let amountPaid = 0;
        const paymentDetailsContainer = document.getElementById('individualPayments');
        paymentDetailsContainer.innerHTML = '';

        paymentAmounts.forEach(input => {
            const amount = parseFloat(input.value) || 0;
            amountPaid += amount;

            const paymentRow = document.createElement('p');
            const paymentType = input.closest('.row').querySelector('select').value;
            if (paymentType && amount) {
                paymentRow.innerText = `${paymentType.toUpperCase()}: ₹${amount.toFixed(2)}`;
                paymentDetailsContainer.appendChild(paymentRow);
            }
        });

        const balance = totalAmount - amountPaid;

        document.getElementById('totalAmountDisplay').innerText = totalAmount.toFixed(2);
        document.getElementById('amountPaidDisplay').innerText = amountPaid.toFixed(2);
        document.getElementById('balanceDisplay').innerText = balance.toFixed(2);
    };

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
    @if(isset($newVehicleSale) && $newVehicleSale->payments)
        @foreach($newVehicleSale->payments as $payment)
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
