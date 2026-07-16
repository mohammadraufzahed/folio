<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = (new \Folio\Pdf\Template\TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$pdf = $engine->renderFile(__DIR__ . '/templates/resume.folio', [
    'name' => 'Sarah Chen',
    'title' => 'Senior Software Engineer',
    'contact' => 'sarah.chen@example.com  555-123-4567  San Francisco, CA',
    'summary' => 'Experienced software engineer with a passion for building clean, scalable systems and delightful user experiences.',
    'experience' => [
        [
            'roleCompany' => 'Senior Engineer at TechCorp',
            'dates' => '2020 - Present',
            'bullets' => [
                'Led the migration to a microservices architecture.',
                'Improved CI/CD pipelines and reduced build times by 40%.',
            ],
        ],
        [
            'roleCompany' => 'Software Engineer at StartUp Inc',
            'dates' => '2017 - 2020',
            'bullets' => [
                'Built the core payments platform.',
                'Mentored junior engineers.',
            ],
        ],
    ],
    'education' => [
        ['schoolDegree' => 'B.S. Computer Science, Stanford University'],
        ['schoolDegree' => 'M.S. Software Engineering, UC Berkeley'],
    ],
    'skills' => [
        ['name' => 'PHP', 'level' => 'Expert'],
        ['name' => 'Python', 'level' => 'Advanced'],
        ['name' => 'System Design', 'level' => 'Advanced'],
        ['name' => 'Leadership', 'level' => 'Intermediate'],
    ],
]);

file_put_contents(__DIR__ . '/resume.pdf', $pdf);

echo "Generated resume.pdf\n";
