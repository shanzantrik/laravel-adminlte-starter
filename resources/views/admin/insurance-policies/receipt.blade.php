<!DOCTYPE html>
<html>

<head>
  <title>Receipt #{{ $insurancePolicy->id }}</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 20px;
    }

    .receipt {
      max-width: 800px;
      margin: auto;
      border: 1px solid #000;
      padding: 20px;
    }

    .header {
      text-align: left;
      margin-bottom: 20px;
    }

    .company-name {
      font-size: 18px;
      font-weight: bold;
    }

    .company-address {
      font-size: 14px;
    }

    .receipt-details {
      text-align: right;
      margin-bottom: 20px;
    }

    .content {
      margin-bottom: 20px;
      line-height: 1.6;
    }

    .footer {
      text-align: right;
      margin-top: 40px;
    }

    @media print {
      .no-print {
        display: none;
      }
    }
  </style>
</head>

<body>
  <div class="receipt">
    <div class="header">
      <div class="company-name">GARGYA AUTOCITY PVT. LTD.</div>
      <div class="company-address">
        Opp. D.T.O. Kamrup, NH-37, Guwahati Bye Pass,<br>
        Betkuchi, Guwahati - 781040<br>
        Toll Free No : 18001236670, Fax: 0361-2236623
      </div>
    </div>

    <div class="receipt-details">
      <strong>Receipt No. {{ date('Y') . sprintf('%08d', $insurancePolicy->id) }}</strong><br>
      Date: {{ date('d-m-Y') }}
    </div>

    <div class="content">
      Received with thanks from {{ $insurancePolicy->customer->name }} an amount of ₹{{
      number_format($insurancePolicy->amount_paid, 2) }}
      (in words Rupees {{ ucwords($amountInWords) }} only) against Insurance Policy vide
      @foreach($insurancePolicy->payments as $payment)
      {{ ucwords(str_replace('_', ' ', $payment->payment_by)) }}: ₹{{ number_format($payment->amount, 2) }}
      @if(!$loop->last), @endif
      @endforeach
      <br><br>
      Proposal/Policy Number: {{ $insurancePolicy->proposal_policy_number }}
    </div>

    <div class="footer">
      For GARGYA AUTOCITY PVT. LTD.
    </div>
  </div>

  <div class="no-print" style="text-align: center; margin-top: 20px;">
    <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
    <button onclick="window.close()" class="btn btn-secondary">Close</button>
  </div>
</body>

</html>
