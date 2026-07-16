<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = (new \Folio\Pdf\Template\TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$company = [
    'name' => 'Acme Operations Ltd.',
    'address' => '100 Enterprise Way, San Francisco, CA 94107',
];

$report = [
    'title' => 'Company Operations Report',
    'period' => 'Fiscal Year 2024',
    'generatedAt' => 'Generated on ' . date('F j, Y'),
];

$summary = [
    ['label' => 'REVENUE', 'value' => '$48.2M', 'change' => '+12.4%'],
    ['label' => 'PROFIT', 'value' => '$12.8M', 'change' => '+8.1%'],
    ['label' => 'EMPLOYEES', 'value' => '342', 'change' => '+24'],
    ['label' => 'CUSTOMERS', 'value' => '1,240', 'change' => '+18%'],
    ['label' => 'CSAT', 'value' => '94%', 'change' => '+2%'],
];

$departments = [
    ['name' => 'Engineering', 'head' => 'Dr. Sarah Chen', 'headcount' => '85', 'budget' => '$5.2M'],
    ['name' => 'Sales', 'head' => 'Michael Ross', 'headcount' => '42', 'budget' => '$2.1M'],
    ['name' => 'Marketing', 'head' => 'Emily Davis', 'headcount' => '28', 'budget' => '$1.8M'],
    ['name' => 'Operations', 'head' => 'James Wilson', 'headcount' => '56', 'budget' => '$3.4M'],
    ['name' => 'Support', 'head' => 'Linda Martinez', 'headcount' => '64', 'budget' => '$1.9M'],
    ['name' => 'HR', 'head' => 'Robert Brown', 'headcount' => '18', 'budget' => '$0.9M'],
];

$employees = [];
for ($i = 1; $i <= 10; $i++) {
    $employees[] = [
        'id' => 'E' . str_pad((string) $i, 3, '0', \STR_PAD_LEFT),
        'name' => 'Employee ' . $i,
        'role' => 'Role ' . $i,
        'department' => 'Department ' . ($i % 4 + 1),
        'salary' => '$' . number_format(50000 + ($i * 5000), 2),
    ];
}

$products = [];
for ($i = 1; $i <= 8; $i++) {
    $products[] = [
        'sku' => 'SKU-' . str_pad((string) $i, 3, '0', \STR_PAD_LEFT),
        'name' => 'Product ' . $i,
        'category' => 'Category ' . ($i % 3 + 1),
        'price' => '$' . number_format(10 + $i * 5, 2),
        'stock' => (string) (100 - $i * 7),
    ];
}

$sales = [
    ['product' => 'Widget A', 'units' => '1,240', 'revenue' => '$12.4K', 'region' => 'North America'],
    ['product' => 'Widget B', 'units' => '980', 'revenue' => '$9.8K', 'region' => 'Europe'],
    ['product' => 'Widget C', 'units' => '760', 'revenue' => '$7.6K', 'region' => 'Asia'],
    ['product' => 'Widget D', 'units' => '540', 'revenue' => '$5.4K', 'region' => 'South America'],
    ['product' => 'Widget E', 'units' => '320', 'revenue' => '$3.2K', 'region' => 'Africa'],
    ['product' => 'Widget F', 'units' => '410', 'revenue' => '$4.1K', 'region' => 'Oceania'],
];

$inventory = [
    ['warehouse' => 'West', 'sku' => 'SKU-001', 'onHand' => '120', 'reserved' => '20', 'available' => '100'],
    ['warehouse' => 'East', 'sku' => 'SKU-002', 'onHand' => '95', 'reserved' => '15', 'available' => '80'],
    ['warehouse' => 'Central', 'sku' => 'SKU-003', 'onHand' => '200', 'reserved' => '50', 'available' => '150'],
    ['warehouse' => 'South', 'sku' => 'SKU-004', 'onHand' => '60', 'reserved' => '10', 'available' => '50'],
    ['warehouse' => 'North', 'sku' => 'SKU-005', 'onHand' => '80', 'reserved' => '5', 'available' => '75'],
    ['warehouse' => 'International', 'sku' => 'SKU-006', 'onHand' => '150', 'reserved' => '30', 'available' => '120'],
];

$customers = [
    ['name' => 'Global Tech', 'segment' => 'Enterprise', 'country' => 'USA', 'ltv' => '$450K'],
    ['name' => 'Startup X', 'segment' => 'SMB', 'country' => 'UK', 'ltv' => '$85K'],
    ['name' => 'MegaCorp', 'segment' => 'Enterprise', 'country' => 'Germany', 'ltv' => '$920K'],
    ['name' => 'Design Studio', 'segment' => 'Agency', 'country' => 'France', 'ltv' => '$120K'],
    ['name' => 'Retail Plus', 'segment' => 'Retail', 'country' => 'Canada', 'ltv' => '$210K'],
    ['name' => 'Finance Hub', 'segment' => 'Finance', 'country' => 'Japan', 'ltv' => '$760K'],
];

$invoices = [];
for ($i = 1; $i <= 6; $i++) {
    $invoices[] = [
        'number' => 'INV-' . str_pad((string) $i, 4, '0', \STR_PAD_LEFT),
        'customer' => 'Customer ' . $i,
        'amount' => '$' . number_format(1000 + $i * 250, 2),
        'status' => $i % 2 === 0 ? 'Paid' : 'Pending',
        'dueDate' => '2024-' . str_pad((string) (12 - $i), 2, '0', \STR_PAD_LEFT) . '-15',
    ];
}

$expenses = [
    ['category' => 'Software', 'vendor' => 'SaaS Corp', 'amount' => '$4,500', 'month' => '2024-06'],
    ['category' => 'Hardware', 'vendor' => 'Tech Supply', 'amount' => '$12,000', 'month' => '2024-06'],
    ['category' => 'Travel', 'vendor' => 'Business Travel Inc', 'amount' => '$3,200', 'month' => '2024-06'],
    ['category' => 'Marketing', 'vendor' => 'Ad Partners', 'amount' => '$8,000', 'month' => '2024-06'],
    ['category' => 'Rent', 'vendor' => 'Office Properties', 'amount' => '$25,000', 'month' => '2024-06'],
    ['category' => 'Utilities', 'vendor' => 'City Power', 'amount' => '$1,800', 'month' => '2024-06'],
];

$projects = [
    ['name' => 'Platform 2.0', 'owner' => 'Sarah Chen', 'status' => 'In Progress', 'progress' => '75%', 'budget' => '$1.2M'],
    ['name' => 'Mobile App', 'owner' => 'Michael Ross', 'status' => 'Planning', 'progress' => '20%', 'budget' => '$800K'],
    ['name' => 'Data Warehouse', 'owner' => 'James Wilson', 'status' => 'Completed', 'progress' => '100%', 'budget' => '$2.0M'],
    ['name' => 'AI Assistant', 'owner' => 'Emily Davis', 'status' => 'In Progress', 'progress' => '40%', 'budget' => '$1.5M'],
    ['name' => 'Security Audit', 'owner' => 'Linda Martinez', 'status' => 'In Progress', 'progress' => '60%', 'budget' => '$300K'],
];

$metrics = [
    ['name' => 'Uptime', 'target' => '99.99%', 'actual' => '99.97%', 'status' => 'Good'],
    ['name' => 'Response Time', 'target' => '<200ms', 'actual' => '180ms', 'status' => 'Good'],
    ['name' => 'NPS', 'target' => '70', 'actual' => '72', 'status' => 'Good'],
    ['name' => 'Churn', 'target' => '<5%', 'actual' => '4.2%', 'status' => 'Good'],
    ['name' => 'Support SLA', 'target' => '95%', 'actual' => '96%', 'status' => 'Good'],
];

$regions = [
    ['name' => 'North America', 'revenue' => '$24.1M', 'growth' => '+14%', 'customers' => '620'],
    ['name' => 'Europe', 'revenue' => '$14.5M', 'growth' => '+9%', 'customers' => '380'],
    ['name' => 'Asia Pacific', 'revenue' => '$7.2M', 'growth' => '+22%', 'customers' => '190'],
    ['name' => 'Latin America', 'revenue' => '$2.4M', 'growth' => '+6%', 'customers' => '50'],
];

$vendors = [
    ['name' => 'CloudHost Pro', 'category' => 'Cloud', 'rating' => '4.8', 'contractEnd' => '2025-03'],
    ['name' => 'Office Supplies Co', 'category' => 'Office', 'rating' => '4.2', 'contractEnd' => '2024-12'],
    ['name' => 'Legal Advisors', 'category' => 'Legal', 'rating' => '4.9', 'contractEnd' => '2025-06'],
    ['name' => 'Print Solutions', 'category' => 'Print', 'rating' => '4.0', 'contractEnd' => '2024-09'],
    ['name' => 'Travel Partners', 'category' => 'Travel', 'rating' => '4.5', 'contractEnd' => '2025-01'],
];

$supportTickets = [];
for ($i = 1; $i <= 6; $i++) {
    $supportTickets[] = [
        'id' => 'TKT-' . str_pad((string) $i, 4, '0', \STR_PAD_LEFT),
        'priority' => $i % 3 === 0 ? 'High' : 'Normal',
        'customer' => 'Customer ' . $i,
        'status' => $i % 2 === 0 ? 'Resolved' : 'Open',
        'assignee' => 'Agent ' . $i,
    ];
}

$pdf = $engine->renderFile(__DIR__ . '/templates/company-report.folio', [
    'company' => $company,
    'report' => $report,
    'summary' => $summary,
    'departments' => $departments,
    'employees' => $employees,
    'products' => $products,
    'sales' => $sales,
    'inventory' => $inventory,
    'customers' => $customers,
    'invoices' => $invoices,
    'expenses' => $expenses,
    'projects' => $projects,
    'metrics' => $metrics,
    'regions' => $regions,
    'vendors' => $vendors,
    'supportTickets' => $supportTickets,
]);

file_put_contents(__DIR__ . '/company-report.pdf', $pdf);

echo "Generated company-report.pdf\n";
