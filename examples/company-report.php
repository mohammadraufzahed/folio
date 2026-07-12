<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\PhpTemplateCompiler;

/**
 * Build sample report data that will be injected into the .folio template.
 *
 * @return array<string, mixed>
 */
function buildReportData(): array
{
    return [
        'company' => [
            'name' => 'Acme Robotics Inc.',
        ],
        'report' => [
            'title' => 'Annual Operations Report',
            'period' => 'FY 2025 / Q1-Q4',
            'generatedAt' => 'Generated: ' . date('Y-m-d H:i'),
        ],
        'summary' => [
            ['label' => 'Total Revenue', 'value' => '$48.2M', 'change' => '+12%'],
            ['label' => 'Gross Margin', 'value' => '41%', 'change' => '+2pp'],
            ['label' => 'Active Customers', 'value' => '1,284', 'change' => '+9%'],
            ['label' => 'NPS', 'value' => '62', 'change' => '+4'],
            ['label' => 'Headcount', 'value' => '312', 'change' => '+18'],
        ],
        'departments' => [
            ['name' => 'Engineering', 'head' => 'A. Nguyen', 'headcount' => '96', 'budget' => '$12.4M'],
            ['name' => 'Sales', 'head' => 'M. Alvarez', 'headcount' => '54', 'budget' => '$6.1M'],
            ['name' => 'Support', 'head' => 'R. Patel', 'headcount' => '41', 'budget' => '$3.2M'],
            ['name' => 'Marketing', 'head' => 'S. Kim', 'headcount' => '28', 'budget' => '$4.0M'],
            ['name' => 'Finance', 'head' => 'J. Brooks', 'headcount' => '19', 'budget' => '$2.1M'],
            ['name' => 'Operations', 'head' => 'L. Costa', 'headcount' => '74', 'budget' => '$8.5M'],
        ],
        'employees' => array_map(
            static fn(int $i): array => [
                'id' => sprintf('E%04d', 1000 + $i),
                'name' => ['Alex Reed', 'Jordan Lee', 'Sam Ortiz', 'Taylor Quinn', 'Casey Bloom', 'Riley Chen', 'Morgan Diaz', 'Avery Fox'][$i % 8],
                'role' => ['Engineer', 'Account Exec', 'Support', 'Designer', 'Analyst'][$i % 5],
                'department' => ['Engineering', 'Sales', 'Support', 'Marketing', 'Finance'][$i % 5],
                'salary' => '$' . number_format(65000 + ($i * 1750), 0),
            ],
            range(0, 11)
        ),
        'products' => [
            ['sku' => 'RB-100', 'name' => 'Rover Base', 'category' => 'Hardware', 'price' => '$4,200', 'stock' => '84'],
            ['sku' => 'RB-200', 'name' => 'Rover Pro', 'category' => 'Hardware', 'price' => '$7,800', 'stock' => '41'],
            ['sku' => 'SN-10', 'name' => 'Lidar Sensor', 'category' => 'Sensors', 'price' => '$920', 'stock' => '260'],
            ['sku' => 'SN-22', 'name' => 'Depth Cam', 'category' => 'Sensors', 'price' => '$640', 'stock' => '190'],
            ['sku' => 'SW-AI', 'name' => 'Nav AI License', 'category' => 'Software', 'price' => '$1,200', 'stock' => '∞'],
            ['sku' => 'KT-01', 'name' => 'Field Kit', 'category' => 'Accessories', 'price' => '$310', 'stock' => '500'],
            ['sku' => 'BT-9', 'name' => 'Battery Pack', 'category' => 'Power', 'price' => '$480', 'stock' => '320'],
            ['sku' => 'CH-3', 'name' => 'Dock Charger', 'category' => 'Power', 'price' => '$890', 'stock' => '110'],
        ],
        'sales' => [
            ['product' => 'Rover Pro', 'units' => '120', 'revenue' => '$936K', 'region' => 'NA'],
            ['product' => 'Rover Base', 'units' => '210', 'revenue' => '$882K', 'region' => 'EU'],
            ['product' => 'Nav AI License', 'units' => '540', 'revenue' => '$648K', 'region' => 'APAC'],
            ['product' => 'Lidar Sensor', 'units' => '890', 'revenue' => '$819K', 'region' => 'NA'],
            ['product' => 'Field Kit', 'units' => '1,400', 'revenue' => '$434K', 'region' => 'LATAM'],
            ['product' => 'Battery Pack', 'units' => '760', 'revenue' => '$365K', 'region' => 'EU'],
        ],
        'inventory' => [
            ['warehouse' => 'Austin', 'sku' => 'RB-100', 'onHand' => '40', 'reserved' => '8', 'available' => '32'],
            ['warehouse' => 'Austin', 'sku' => 'RB-200', 'onHand' => '22', 'reserved' => '5', 'available' => '17'],
            ['warehouse' => 'Berlin', 'sku' => 'SN-10', 'onHand' => '120', 'reserved' => '30', 'available' => '90'],
            ['warehouse' => 'Singapore', 'sku' => 'BT-9', 'onHand' => '200', 'reserved' => '45', 'available' => '155'],
            ['warehouse' => 'Singapore', 'sku' => 'CH-3', 'onHand' => '60', 'reserved' => '12', 'available' => '48'],
            ['warehouse' => 'São Paulo', 'sku' => 'KT-01', 'onHand' => '300', 'reserved' => '20', 'available' => '280'],
        ],
        'customers' => [
            ['name' => 'NorthGrid Energy', 'segment' => 'Enterprise', 'country' => 'USA', 'ltv' => '$1.2M'],
            ['name' => 'Helix Logistics', 'segment' => 'Mid-Market', 'country' => 'Germany', 'ltv' => '$480K'],
            ['name' => 'Orbit Farms', 'segment' => 'SMB', 'country' => 'Brazil', 'ltv' => '$95K'],
            ['name' => 'Kite Medical', 'segment' => 'Enterprise', 'country' => 'Japan', 'ltv' => '$760K'],
            ['name' => 'Blue Harbor', 'segment' => 'Mid-Market', 'country' => 'UK', 'ltv' => '$310K'],
            ['name' => 'Summit Mining', 'segment' => 'Enterprise', 'country' => 'Australia', 'ltv' => '$890K'],
        ],
        'invoices' => [
            ['number' => 'INV-2401', 'customer' => 'NorthGrid Energy', 'amount' => '$120,000', 'status' => 'Paid', 'dueDate' => '2025-01-15'],
            ['number' => 'INV-2402', 'customer' => 'Helix Logistics', 'amount' => '$48,500', 'status' => 'Open', 'dueDate' => '2025-02-01'],
            ['number' => 'INV-2403', 'customer' => 'Orbit Farms', 'amount' => '$12,300', 'status' => 'Overdue', 'dueDate' => '2025-01-05'],
            ['number' => 'INV-2404', 'customer' => 'Kite Medical', 'amount' => '$88,000', 'status' => 'Paid', 'dueDate' => '2025-01-20'],
            ['number' => 'INV-2405', 'customer' => 'Blue Harbor', 'amount' => '$27,750', 'status' => 'Open', 'dueDate' => '2025-02-12'],
            ['number' => 'INV-2406', 'customer' => 'Summit Mining', 'amount' => '$150,000', 'status' => 'Paid', 'dueDate' => '2025-01-28'],
        ],
        'expenses' => [
            ['category' => 'Cloud', 'vendor' => 'AWS', 'amount' => '$86,000', 'month' => 'Jan'],
            ['category' => 'Cloud', 'vendor' => 'GCP', 'amount' => '$41,000', 'month' => 'Jan'],
            ['category' => 'Travel', 'vendor' => 'Delta', 'amount' => '$18,400', 'month' => 'Feb'],
            ['category' => 'Hardware', 'vendor' => 'DigiKey', 'amount' => '$62,200', 'month' => 'Feb'],
            ['category' => 'Marketing', 'vendor' => 'Meta Ads', 'amount' => '$33,000', 'month' => 'Mar'],
            ['category' => 'Facilities', 'vendor' => 'WeWork', 'amount' => '$29,500', 'month' => 'Mar'],
        ],
        'projects' => [
            ['name' => 'NavStack 3.0', 'owner' => 'A. Nguyen', 'status' => 'On Track', 'progress' => '72%', 'budget' => '$2.1M'],
            ['name' => 'EU Expansion', 'owner' => 'M. Alvarez', 'status' => 'At Risk', 'progress' => '48%', 'budget' => '$1.4M'],
            ['name' => 'Support Portal', 'owner' => 'R. Patel', 'status' => 'On Track', 'progress' => '85%', 'budget' => '$420K'],
            ['name' => 'Battery Gen2', 'owner' => 'L. Costa', 'status' => 'Delayed', 'progress' => '39%', 'budget' => '$980K'],
            ['name' => 'Partner API', 'owner' => 'S. Kim', 'status' => 'On Track', 'progress' => '61%', 'budget' => '$310K'],
        ],
        'metrics' => [
            ['name' => 'MRR', 'target' => '$4.0M', 'actual' => '$4.2M', 'status' => 'Hit'],
            ['name' => 'Churn', 'target' => '< 2%', 'actual' => '1.6%', 'status' => 'Hit'],
            ['name' => 'Gross Margin', 'target' => '40%', 'actual' => '41%', 'status' => 'Hit'],
            ['name' => 'Support CSAT', 'target' => '90%', 'actual' => '88%', 'status' => 'Miss'],
            ['name' => 'Deploy Frequency', 'target' => '20/wk', 'actual' => '24/wk', 'status' => 'Hit'],
        ],
        'regions' => [
            ['name' => 'North America', 'revenue' => '$21.4M', 'growth' => '+11%', 'customers' => '520'],
            ['name' => 'Europe', 'revenue' => '$14.8M', 'growth' => '+9%', 'customers' => '410'],
            ['name' => 'APAC', 'revenue' => '$8.1M', 'growth' => '+18%', 'customers' => '240'],
            ['name' => 'LATAM', 'revenue' => '$3.9M', 'growth' => '+22%', 'customers' => '114'],
        ],
        'vendors' => [
            ['name' => 'AWS', 'category' => 'Cloud', 'rating' => 'A', 'contractEnd' => '2026-06'],
            ['name' => 'DigiKey', 'category' => 'Components', 'rating' => 'A-', 'contractEnd' => '2025-12'],
            ['name' => 'DHL', 'category' => 'Logistics', 'rating' => 'B+', 'contractEnd' => '2025-09'],
            ['name' => 'Okta', 'category' => 'Security', 'rating' => 'A', 'contractEnd' => '2026-03'],
            ['name' => 'Figma', 'category' => 'Design', 'rating' => 'A', 'contractEnd' => '2025-11'],
        ],
        'supportTickets' => [
            ['id' => 'T-901', 'priority' => 'High', 'customer' => 'Helix Logistics', 'status' => 'Open', 'assignee' => 'Casey'],
            ['id' => 'T-902', 'priority' => 'Med', 'customer' => 'Orbit Farms', 'status' => 'Pending', 'assignee' => 'Riley'],
            ['id' => 'T-903', 'priority' => 'Low', 'customer' => 'Blue Harbor', 'status' => 'Resolved', 'assignee' => 'Avery'],
            ['id' => 'T-904', 'priority' => 'High', 'customer' => 'Summit Mining', 'status' => 'Open', 'assignee' => 'Jordan'],
            ['id' => 'T-905', 'priority' => 'Med', 'customer' => 'Kite Medical', 'status' => 'Open', 'assignee' => 'Morgan'],
            ['id' => 'T-906', 'priority' => 'Low', 'customer' => 'NorthGrid Energy', 'status' => 'Resolved', 'assignee' => 'Sam'],
        ],
        'quarters' => [
            ['name' => 'Q1', 'revenue' => '$10.2M', 'expenses' => '$7.1M', 'profit' => '$3.1M'],
            ['name' => 'Q2', 'revenue' => '$11.5M', 'expenses' => '$7.6M', 'profit' => '$3.9M'],
            ['name' => 'Q3', 'revenue' => '$12.8M', 'expenses' => '$8.0M', 'profit' => '$4.8M'],
            ['name' => 'Q4', 'revenue' => '$13.7M', 'expenses' => '$8.4M', 'profit' => '$5.3M'],
        ],
        'channels' => [
            ['name' => 'Paid Search', 'spend' => '$420K', 'leads' => '3,100', 'cac' => '$135'],
            ['name' => 'Events', 'spend' => '$280K', 'leads' => '860', 'cac' => '$325'],
            ['name' => 'Partners', 'spend' => '$150K', 'leads' => '1,240', 'cac' => '$121'],
            ['name' => 'Content', 'spend' => '$95K', 'leads' => '2,050', 'cac' => '$46'],
            ['name' => 'Outbound', 'spend' => '$210K', 'leads' => '740', 'cac' => '$284'],
        ],
        'training' => [
            ['name' => 'Safety Level 1', 'attendees' => '180', 'hours' => '4', 'score' => '94%'],
            ['name' => 'Robot Ops', 'attendees' => '96', 'hours' => '16', 'score' => '91%'],
            ['name' => 'Security Awareness', 'attendees' => '312', 'hours' => '2', 'score' => '97%'],
            ['name' => 'Sales Playbook', 'attendees' => '54', 'hours' => '8', 'score' => '89%'],
        ],
        'compliance' => [
            ['control' => 'SOC2 Access Reviews', 'owner' => 'Security', 'due' => '2025-03-01', 'state' => 'Complete'],
            ['control' => 'GDPR DPIA', 'owner' => 'Legal', 'due' => '2025-03-15', 'state' => 'In Progress'],
            ['control' => 'ISO 9001 Audit', 'owner' => 'QA', 'due' => '2025-04-01', 'state' => 'Planned'],
            ['control' => 'Backup Restore Test', 'owner' => 'SRE', 'due' => '2025-02-20', 'state' => 'Complete'],
        ],
        'assets' => [
            ['name' => 'CNC Cell A', 'type' => 'Manufacturing', 'location' => 'Austin', 'value' => '$420K'],
            ['name' => 'Test Fleet', 'type' => 'R&D', 'location' => 'Austin', 'value' => '$310K'],
            ['name' => 'EU Demo Units', 'type' => 'Sales', 'location' => 'Berlin', 'value' => '$180K'],
            ['name' => 'GPU Cluster', 'type' => 'Compute', 'location' => 'Cloud', 'value' => '$260K'],
        ],
        'notes' => [
            ['author' => 'CEO', 'text' => 'Strong year; double down on APAC partners.', 'date' => '2025-12-18'],
            ['author' => 'CFO', 'text' => 'Margin expansion continues; watch cloud spend.', 'date' => '2025-12-18'],
            ['author' => 'COO', 'text' => 'Battery Gen2 delay needs recovery plan in Q1.', 'date' => '2025-12-19'],
            ['author' => 'CRO', 'text' => 'Enterprise pipeline healthy entering next FY.', 'date' => '2025-12-19'],
        ],
    ];
}

$compiler = new PhpTemplateCompiler();
$templatePath = __DIR__ . '/templates/company-report.folio';
$data = buildReportData();

echo "Compiling template with data binding...\n";
$php = $compiler->compileFile($templatePath);
echo "Compiled bytes: " . strlen($php) . "\n";

// Show a snippet of generated PHP proving data binding
if (str_contains($php, 'function (array $data')) {
    echo "OK: compiled template accepts array \$data\n";
}
if (str_contains($php, 'foreach')) {
    echo "OK: foreach loops present in compiled output\n";
}

$pdf = $compiler->renderFile($templatePath, $data);
$out = __DIR__ . '/company-report.pdf';
$pdf->save($out);

echo "PDF saved: {$out}\n";
echo "Pages: multi-page report with 20 tables driven by PHP data\n";
