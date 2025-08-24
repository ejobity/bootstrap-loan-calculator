# bootstrap-loan-calculator
A comprehensive loan calculator with Bootstrap styling, pie chart visualization, and amortization schedule. This is a wordpress plugin.


## Key Features:

1.  **Responsive Bootstrap Design** - Professional, mobile-friendly interface
2.  **Comprehensive Input Fields:**
    -   Loan amount (text field with validation)
    -   Interest rate (annual percentage)
    -   Loan term (with years/months selector)
    -   Payment frequency (monthly, weekly, quarterly, semi-annually, annually)
3.  **Results Display:**
    -   Payment amount per period
    -   Number of total payments
    -   Total principal and interest breakdown
    -   Total amount to be paid
    -   Interactive pie chart showing principal vs interest ratio
4.  **Amortization Schedule:**
    -   Complete payment schedule table
    -   Payment dates calculated based on frequency
    -   Principal and interest breakdown for each payment
    -   Remaining balance after each payment

## Technical Features:

-   **CDN Integration:** Uses Bootstrap 5.3.0 and Chart.js from CDN
-   **Client-side Calculations:** Fast, responsive calculations using JavaScript
-   **Security:** Includes nonce verification for AJAX requests
-   **Responsive Design:** Works perfectly on desktop and mobile devices
-   **Professional Styling:** Modern gradient effects, hover animations, and clean layout

## How to Use:

1.  **Installation:** Save this code as a PHP file (e.g., `loan-calculator.php`) in your WordPress plugins directory (`/wp-content/plugins/bootstrap-loan-calculator/`)
2.  **Activation:** Go to WordPress admin → Plugins → Activate "Bootstrap Loan Calculator"
3.  **Usage:** Add the shortcode `[bootstrap_loan_calculator]` to any post, page, or widget where you want the calculator to appear

## Plugin Structure:

-   **Main Class:** `BootstrapLoanCalculator` handles all plugin functionality
-   **Shortcode:** `[bootstrap_loan_calculator]` renders the calculator interface
-   **Scripts & Styles:** Automatically enqueues Bootstrap, Chart.js, and custom styling
-   **Calculations:** Uses standard loan amortization formulas (PMT function)
-   **Security:** Includes WordPress nonce verification

The calculator automatically handles different payment frequencies and provides accurate calculations for all scenarios. The amortization table shows the complete payment schedule with dates, and the pie chart provides a visual representation of the principal vs interest distribution.

![bootstrap-loan-calculator](https://loanfren.com/wp-content/uploads/2025/08/bootstrap-loan-calculator.jpg)
