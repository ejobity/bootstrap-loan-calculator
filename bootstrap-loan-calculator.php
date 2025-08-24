<?php
/**
 * Plugin Name: Bootstrap Loan Calculator
 * Plugin URI: https://yoursite.com/
 * Description: A comprehensive loan calculator with Bootstrap styling, pie chart visualization, and amortization schedule.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BootstrapLoanCalculator {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('bootstrap_loan_calculator', array($this, 'render_calculator'));
        add_action('wp_ajax_calculate_loan', array($this, 'ajax_calculate_loan'));
        add_action('wp_ajax_nopriv_calculate_loan', array($this, 'ajax_calculate_loan'));
    }
    
    public function init() {
        // Plugin initialization
    }
    
    public function enqueue_scripts() {
        // Bootstrap CSS
        wp_enqueue_style('bootstrap-css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css');
        
        // Bootstrap JS
        wp_enqueue_script('bootstrap-js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
        
        // Chart.js
        wp_enqueue_script('chartjs', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js', array(), '3.9.1', true);
        
        // Plugin custom JS
        wp_enqueue_script('loan-calculator-js', plugin_dir_url(__FILE__) . 'loan-calculator.js', array('jquery', 'chartjs'), '1.0.0', true);
        
        // Localize script for AJAX
        wp_localize_script('loan-calculator-js', 'loan_calculator_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('loan_calculator_nonce')
        ));
        
        // Plugin custom CSS
        wp_enqueue_style('loan-calculator-css', plugin_dir_url(__FILE__) . 'loan-calculator.css', array(), '1.0.0');
    }
    
    public function render_calculator($atts) {
        ob_start();
        ?>
        <div class="container-fluid loan-calculator-container">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0"><i class="fas fa-calculator"></i> Loan Calculator</h3>
                        </div>
                        <div class="card-body">
                            <form id="loanCalculatorForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="loanAmount" class="form-label">Loan Amount ($)</label>
                                            <input type="number" class="form-control" id="loanAmount" step="0.01" min="0" placeholder="10000" required>
                                            <div class="form-text">Enter the total loan amount</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="interestRate" class="form-label">Annual Interest Rate (%)</label>
                                            <input type="number" class="form-control" id="interestRate" step="0.01" min="0" placeholder="5.5" required>
                                            <div class="form-text">Enter annual interest rate as percentage</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="loanTerm" class="form-label">Loan Term</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="loanTerm" min="1" placeholder="30" required>
                                                <select class="form-select" id="termUnit" style="max-width: 120px;">
                                                    <option value="years">Years</option>
                                                    <option value="months">Months</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="paymentFrequency" class="form-label">Payment Frequency</label>
                                            <select class="form-select" id="paymentFrequency" required>
                                                <option value="monthly">Monthly</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="quarterly">Quarterly</option>
                                                <option value="semi-annually">Every 6 Months</option>
                                                <option value="annually">Annually</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-calculator"></i> Calculate Loan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Results Section -->
            <div class="row mt-4" id="resultsSection" style="display: none;">
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0"><i class="fas fa-chart-line"></i> Loan Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="result-item">
                                        <h5>Payment Amount</h5>
                                        <p class="display-6 text-primary" id="paymentAmount">$0.00</p>
                                    </div>
                                    <div class="result-item">
                                        <h5>Total Principal</h5>
                                        <p class="h4" id="totalPrincipal">$0.00</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="result-item">
                                        <h5>Number of Payments</h5>
                                        <p class="display-6 text-info" id="numberOfPayments">0</p>
                                    </div>
                                    <div class="result-item">
                                        <h5>Total Interest</h5>
                                        <p class="h4 text-warning" id="totalInterest">$0.00</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="result-item">
                                        <h5>Total Amount to Pay</h5>
                                        <p class="display-5 text-danger" id="totalAmount">$0.00</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card shadow">
                        <div class="card-header bg-info text-white">
                            <h4 class="mb-0"><i class="fas fa-chart-pie"></i> Principal vs Interest</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="loanChart" width="300" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Amortization Schedule -->
            <div class="row mt-4" id="amortizationSection" style="display: none;">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white">
                            <h4 class="mb-0"><i class="fas fa-table"></i> Amortization Schedule</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="amortizationTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Payment #</th>
                                            <th>Payment Date</th>
                                            <th>Payment Amount</th>
                                            <th>Principal</th>
                                            <th>Interest</th>
                                            <th>Remaining Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody id="amortizationTableBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .loan-calculator-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .result-item {
            margin-bottom: 1rem;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        
        .result-item h5 {
            margin-bottom: 5px;
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .card {
            border: none;
            border-radius: 12px;
        }
        
        .card-header {
            border-radius: 12px 12px 0 0 !important;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        
        #loanChart {
            max-width: 100%;
            height: auto !important;
        }
        
        .table th {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
        
        @media (max-width: 768px) {
            .display-6 {
                font-size: 1.5rem;
            }
            .display-5 {
                font-size: 2rem;
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            let loanChart = null;
            
            $('#loanCalculatorForm').on('submit', function(e) {
                e.preventDefault();
                calculateLoan();
            });
            
            function calculateLoan() {
                const loanAmount = parseFloat($('#loanAmount').val());
                const interestRate = parseFloat($('#interestRate').val()) / 100;
                const loanTerm = parseInt($('#loanTerm').val());
                const termUnit = $('#termUnit').val();
                const paymentFrequency = $('#paymentFrequency').val();
                
                if (!loanAmount || !interestRate || !loanTerm) {
                    alert('Please fill in all required fields');
                    return;
                }
                
                // Convert everything to months for calculation
                let totalMonths = termUnit === 'years' ? loanTerm * 12 : loanTerm;
                let paymentsPerYear;
                let paymentFrequencyText;
                
                switch(paymentFrequency) {
                    case 'weekly':
                        paymentsPerYear = 52;
                        paymentFrequencyText = 'Weekly';
                        break;
                    case 'monthly':
                        paymentsPerYear = 12;
                        paymentFrequencyText = 'Monthly';
                        break;
                    case 'quarterly':
                        paymentsPerYear = 4;
                        paymentFrequencyText = 'Quarterly';
                        break;
                    case 'semi-annually':
                        paymentsPerYear = 2;
                        paymentFrequencyText = 'Semi-Annually';
                        break;
                    case 'annually':
                        paymentsPerYear = 1;
                        paymentFrequencyText = 'Annually';
                        break;
                }
                
                const periodicRate = interestRate / paymentsPerYear;
                const totalPayments = Math.ceil((totalMonths / 12) * paymentsPerYear);
                
                // Calculate payment using PMT formula
                const paymentAmount = loanAmount * (periodicRate * Math.pow(1 + periodicRate, totalPayments)) / 
                                    (Math.pow(1 + periodicRate, totalPayments) - 1);
                
                const totalAmount = paymentAmount * totalPayments;
                const totalInterest = totalAmount - loanAmount;
                
                // Display results
                displayResults(paymentAmount, totalPayments, loanAmount, totalInterest, totalAmount);
                
                // Generate amortization schedule
                generateAmortizationSchedule(loanAmount, periodicRate, paymentAmount, totalPayments, paymentFrequencyText);
                
                // Show results sections
                $('#resultsSection').fadeIn();
                $('#amortizationSection').fadeIn();
            }
            
            function displayResults(payment, numPayments, principal, interest, total) {
                $('#paymentAmount').text('$' + payment.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                $('#numberOfPayments').text(numPayments);
                $('#totalPrincipal').text('$' + principal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                $('#totalInterest').text('$' + interest.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                $('#totalAmount').text('$' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                
                // Create pie chart
                createPieChart(principal, interest);
            }
            
            function createPieChart(principal, interest) {
                const ctx = document.getElementById('loanChart').getContext('2d');
                
                if (loanChart) {
                    loanChart.destroy();
                }
                
                loanChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Principal', 'Interest'],
                        datasets: [{
                            data: [principal, interest],
                            backgroundColor: ['#28a745', '#ffc107'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed;
                                        const total = principal + interest;
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return context.label + ': $' + value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            function generateAmortizationSchedule(loanAmount, periodicRate, paymentAmount, totalPayments, frequency) {
                let balance = loanAmount;
                let tableBody = '';
                let paymentDate = new Date();
                
                // Determine date increment based on frequency
                let dateIncrement;
                switch(frequency) {
                    case 'Weekly':
                        dateIncrement = 7;
                        break;
                    case 'Monthly':
                        dateIncrement = 30;
                        break;
                    case 'Quarterly':
                        dateIncrement = 90;
                        break;
                    case 'Semi-Annually':
                        dateIncrement = 180;
                        break;
                    case 'Annually':
                        dateIncrement = 365;
                        break;
                }
                
                for (let i = 1; i <= totalPayments; i++) {
                    const interestPayment = balance * periodicRate;
                    const principalPayment = Math.min(paymentAmount - interestPayment, balance);
                    balance -= principalPayment;
                    
                    // Format date
                    const formattedDate = paymentDate.toLocaleDateString();
                    
                    tableBody += `
                        <tr>
                            <td>${i}</td>
                            <td>${formattedDate}</td>
                            <td>$${paymentAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</td>
                            <td>$${principalPayment.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</td>
                            <td>$${interestPayment.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</td>
                            <td>$${Math.max(0, balance).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</td>
                        </tr>
                    `;
                    
                    // Increment payment date
                    paymentDate.setDate(paymentDate.getDate() + dateIncrement);
                    
                    if (balance <= 0) break;
                }
                
                $('#amortizationTableBody').html(tableBody);
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    public function ajax_calculate_loan() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'loan_calculator_nonce')) {
            wp_die('Security check failed');
        }
        
        // This method can be used for server-side calculations if needed
        wp_die();
    }
}

// Initialize the plugin
new BootstrapLoanCalculator();

// Activation hook
register_activation_hook(__FILE__, function() {
    // Plugin activation code here
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Plugin deactivation code here
});
?>