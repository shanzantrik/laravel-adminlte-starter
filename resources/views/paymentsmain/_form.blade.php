<div class="row mb-3">
  <div class="col-md-12">
    <div class="float-right">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
          <a href="{{ route('paymentsmain.index') }}" class="text-decoration-none">
            <i class="fas fa-list"></i> View All Payments
          </a>
        </li>
      </ol>
    </div>
  </div>
</div>
<div class="container mt-4">
  <form method="POST" action="{{ route('paymentsmain.store') }}">
    @csrf

    <!-- Payment Type Dropdown -->
    <div class="form-group">
      <label for="payment_type">Payment Type</label>
      <select name="payment_type" id="payment_type" class="form-control" required>
        <option value="">Select Payment Type</option>
        <option value="cash" {{ old('payment_type', $payment->payment_type ?? '') == 'cash' ? 'selected' : '' }}>Cash
        </option>
        <option value="cheque" {{ old('payment_type', $payment->payment_type ?? '') == 'cheque' ? 'selected' : ''
          }}>Cheque</option>
      </select>
    </div>

    <!-- Amount Input -->
    <div class="form-group">
      <label for="amount">Amount</label>
      <input type="number" step="0.01" name="amount" id="amount" class="form-control"
        value="{{ old('amount', $payment->amount ?? '') }}" required>
    </div>

    <!-- Cash Denominations Section -->
    <div id="cash_section" style="display: none;">
      <div class="form-group">
        <label>Denominations</label>
        @php
        $denominations = old('denominations', $payment->denominations ?? []);
        @endphp
        @foreach(['2000', '500', '200', '100', '50', '20', '10', '5', '2', '1'] as $value)
        <div class="input-group mb-2">
          <div class="input-group-prepend">
            <span class="input-group-text">₹{{ $value }}</span>
          </div>
          <input type="number" name="denominations[{{ $value }}]" class="form-control denomination-input"
            value="{{ $denominations[$value] ?? '' }}" min="0">
        </div>
        @endforeach
      </div>
    </div>

    <!-- Cheque Section -->
    <div id="cheque_section" style="display: none;">
      <div id="cheque_boxes"></div>
      <div class="form-group">
        <label for="no_of_cheques">Number of Cheques</label>
        <input type="number" name="no_of_cheques" id="no_of_cheques" class="form-control mb2"
          value="{{ old('no_of_cheques', $payment->no_of_cheques ?? '') }}" min="1">
      </div>

      <div id="cheques_container">
        @if(isset($payment) && $payment->cheques)
        @foreach($payment->cheques as $index => $cheque)
        <div class="cheque-entry border p-3 mb-2">
          <h5>Cheque #{{ $index + 1 }}</h5>
          <div class="form-group">
            <label>Cheque Number</label>
            <input type="text" name="cheques[{{ $index }}][number]" class="form-control"
              value="{{ $cheque->cheque_number }}" required>
          </div>
          <div class="form-group">
            <label>Cheque Date</label>
            <input type="date" name="cheques[{{ $index }}][date]" class="form-control"
              value="{{ $cheque->cheque_date->format('Y-m-d') }}" required>
          </div>
        </div>
        @endforeach
        @endif
      </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary mt-3">SUBMIT</button>
  </form>
</div>

<!-- JavaScript -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
        const paymentType = document.getElementById("payment_type");
        const cashSection = document.getElementById("cash_section");
        const chequeSection = document.getElementById("cheque_section");
        const chequeBoxes = document.getElementById("cheque_boxes");
        const noOfChequesInput = document.getElementById("no_of_cheques");

        // Toggle Sections Based on Payment Type
        paymentType.addEventListener("change", function () {
            if (this.value === "cash") {
                cashSection.style.display = "block";
                chequeSection.style.display = "none";
                chequeBoxes.innerHTML = ""; // Clear previous cheque inputs
            } else if (this.value === "cheque") {
                cashSection.style.display = "none";
                chequeSection.style.display = "block";
            } else {
                cashSection.style.display = "none";
                chequeSection.style.display = "none";
                chequeBoxes.innerHTML = "";
            }
        });

        // Generate Cheque Input Boxes Dynamically
        noOfChequesInput.addEventListener("input", function () {
            chequeBoxes.innerHTML = ""; // Clear previous inputs
            const num = parseInt(this.value) || 0;

            for (let i = 1; i <= num; i++) {
                chequeBoxes.innerHTML += `
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label>Cheque ${i} Number</label>
                            <input type="text" name="cheques[${i}][number]" class="form-control" placeholder="Cheque Number" required>
                        </div>
                        <div class="col-md-6">
                            <label>Cheque ${i} Date</label>
                            <input type="date" name="cheques[${i}][date]" class="form-control" required>
                        </div>
                    </div>
                `;
            }
        });
    });
</script>
