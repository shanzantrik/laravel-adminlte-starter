<div class="container mt-4">
  <form method="POST" action="{{ route('paymentsmain.store') }}">
    @csrf

    <!-- Payment Type Dropdown -->
    <div class="form-group">
      <label for="payment_type"><strong>Payment Type:</strong></label>
      <select id="payment_type" name="payment_type" class="form-control" required>
        <option value="">Select</option>
        <option value="cash">Cash</option>
        <option value="cheque">Cheque</option>
      </select>
    </div>

    <!-- Amount Input -->
    <div class="form-group">
      <label for="amount"><strong>Amount:</strong></label>
      <input type="number" id="amount" name="amount" class="form-control" placeholder="Enter Amount" required>
    </div>

    <!-- Cash Denominations Section -->
    <div id="cash_section" style="display: none;">
      <label><strong>Denomination:</strong></label>
      <div class="row">
        <div class="col"><input type="number" name="denominations[x2000]" placeholder="x2000" class="form-control">
        </div>
        <div class="col"><input type="number" name="denominations[x500]" placeholder="x500" class="form-control"></div>
        <div class="col"><input type="number" name="denominations[x200]" placeholder="x200" class="form-control"></div>
        <div class="col"><input type="number" name="denominations[x100]" placeholder="x100" class="form-control"></div>
      </div>
      <div class="row mt-2">
        <div class="col"><input type="number" name="denominations[x50]" placeholder="x50" class="form-control"></div>
        <div class="col"><input type="number" name="denominations[x20]" placeholder="x20" class="form-control"></div>
        <div class="col"><input type="number" name="denominations[x10]" placeholder="x10" class="form-control"></div>
        <div class="col"><input type="number" name="denominations[coins]" placeholder="Coins" class="form-control">
        </div>
      </div>
    </div>

    <!-- Cheque Inputs Section -->
    <div id="cheque_section" style="display: none;">
      <label for="no_of_cheques"><strong>No. of Cheques:</strong></label>
      <input type="number" id="no_of_cheques" name="no_of_cheques" class="form-control mb-2"
        placeholder="Enter number of cheques">

      <!-- Dynamic Cheque Input Boxes -->
      <div id="cheque_boxes"></div>
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
