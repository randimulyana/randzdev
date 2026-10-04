<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Developer Profile Information
    |--------------------------------------------------------------------------
    |
    | Core personal brand details for the single-author portfolio.
    |
    */
    'profile' => [
        'name' => 'Randi',
        'role' => 'Web Developer & Builder',
        'avatar' => null,
        'location' => 'Indonesia',
        'education' => 'Informatics Engineering',
        'current_focus' => 'Building modern, reliable web applications with Laravel, Tailwind CSS, and elegant architectures.',
        'headline' => 'I build web applications and document what I learn about programming.',
        'bio' => 'Passionate software engineer focused on building production-grade web systems, clean backend architecture, and sharing practical programming knowledge.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Social & Contact Links
    |--------------------------------------------------------------------------
    |
    | Public external links displayed on the homepage hero, about page, and footer.
    |
    */
    'social' => [
        'github' => 'https://github.com/randzdev',
        'linkedin' => 'https://linkedin.com/in/randzdev',
        'email' => 'randi@randz.dev',
        'website' => 'https://randz.dev',
    ],

    /*
    |--------------------------------------------------------------------------
    | Developer Journey Timeline
    |--------------------------------------------------------------------------
    |
    | Chronological milestones representing the developer journey, following
    | the exact progression defined in PRD Section 10.
    |
    */
    'journey' => [
        [
            'period' => 'Phase 1',
            'title' => 'Started Learning',
            'description' => 'Discovered programming fundamentals, computer science basics, and software development logic.',
        ],
        [
            'period' => 'Phase 2',
            'title' => 'First Web Project',
            'description' => 'Built first web applications combining semantic markup, client scripts, and database interactions.',
        ],
        [
            'period' => 'Phase 3',
            'title' => 'Learning PHP',
            'description' => 'Dove deep into modern PHP, object-oriented design patterns, MVC structure, and relational databases.',
        ],
        [
            'period' => 'Phase 4',
            'title' => 'Learning Laravel',
            'description' => 'Mastered Laravel ecosystem, Eloquent ORM, automated testing, authentication, and secure APIs.',
        ],
        [
            'period' => 'Phase 5',
            'title' => 'Building Production Projects',
            'description' => 'Delivered real-world applications, school internship systems, and production web engineering solutions.',
        ],
        [
            'period' => 'Phase 6',
            'title' => 'Documenting & Sharing',
            'description' => 'Created technical articles, architectural case studies, and structured learning tracks for developers.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Categorized Technical Skills
    |--------------------------------------------------------------------------
    |
    | Technical competencies grouped by category without percentage bars,
    | following PRD Section 11.
    |
    */
    'skills' => [
        'Programming' => [
            'PHP',
            'JavaScript',
            'HTML',
            'CSS',
            'SQL',
        ],
        'Frameworks & Libraries' => [
            'Laravel',
            'Tailwind CSS',
            'Alpine.js',
        ],
        'Tools & Environments' => [
            'Git',
            'GitHub',
            'Docker',
            'Linux',
            'MySQL',
            'VS Code',
            'Vite',
        ],
    ],
];
