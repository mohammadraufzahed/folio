<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\TemplateEngine;

$engine = (new TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$pdf = $engine->renderFile(__DIR__ . '/templates/resume.folio', [
    'name' => 'Jordan Lee',
    'title' => 'Senior Software Engineer',
    'contact' => 'jordan.lee@example.com | +1 555 123 4567',
    'summary' => 'Results-driven software engineer with 8+ years building scalable cloud services, developer tools and open-source libraries.',
    'experience' => [
        [
            'roleCompany' => 'Senior Software Engineer at Acme Robotics',
            'dates' => '2021 - Present',
            'bullets' => [
                '- Led the design of a PDF generation engine used company-wide.',
                '- Improved CI/CD pipelines reducing release times by 40%.',
            ],
        ],
        [
            'roleCompany' => 'Software Engineer at NorthGrid Energy',
            'dates' => '2018 - 2021',
            'bullets' => [
                '- Built real-time telemetry ingestion processing 1M events/min.',
                '- Mentored junior engineers and drove code-review best practices.',
            ],
        ],
    ],
    'education' => [
        ['schoolDegree' => 'University of Texas - B.S. Computer Science'],
        ['schoolDegree' => 'Georgia Tech - M.S. Computer Science'],
    ],
    'skills' => [
        ['name' => 'PHP', 'level' => 'Expert'],
        ['name' => 'Distributed Systems', 'level' => 'Advanced'],
        ['name' => 'PDF / PostScript', 'level' => 'Advanced'],
        ['name' => 'Rust', 'level' => 'Intermediate'],
    ],
]);

file_put_contents(__DIR__ . '/resume.pdf', $pdf);

echo 'Resume PDF saved: ' . __DIR__ . '/resume.pdf' . "\n";
