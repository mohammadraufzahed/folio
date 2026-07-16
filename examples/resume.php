<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();

$pdf = $compiler->renderFile(__DIR__ . '/templates/resume.folio', [
    'name' => 'Jordan Lee',
    'title' => 'Senior Software Engineer',
    'contact' => 'jordan.lee@example.com | +1 555 123 4567',
    'summary' => 'Results-driven software engineer with 8+ years building scalable cloud services, developer tools and open-source libraries.',
    'experience' => [
        [
            'role' => 'Senior Software Engineer',
            'company' => 'Acme Robotics',
            'dates' => '2021 - Present',
            'bullets' => [
                'Led the design of a PDF generation engine used company-wide.',
                'Improved CI/CD pipelines reducing release times by 40%.',
            ],
        ],
        [
            'role' => 'Software Engineer',
            'company' => 'NorthGrid Energy',
            'dates' => '2018 - 2021',
            'bullets' => [
                'Built real-time telemetry ingestion pipeline.',
                'Mentored junior engineers and drove code quality initiatives.',
            ],
        ],
    ],
    'education' => [
        ['school' => 'University of Technology', 'degree' => 'B.Sc. Computer Science'],
    ],
    'skills' => [
        ['name' => 'PHP', 'level' => 'Expert'],
        ['name' => 'TypeScript', 'level' => 'Advanced'],
        ['name' => 'Cloud Architecture', 'level' => 'Advanced'],
        ['name' => 'CI/CD', 'level' => 'Advanced'],
    ],
]);

$out = __DIR__ . '/resume.pdf';
$pdf->save($out);

echo "Resume PDF saved: {$out}\n";
