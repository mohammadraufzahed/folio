import { defineConfig } from 'vitepress'
import { readFileSync } from 'node:fs'

const base = process.env.VITEPRESS_BASE || '/folio/'
const folioGrammar = JSON.parse(
  readFileSync(new URL('./shiki/folio.tmLanguage.json', import.meta.url), 'utf-8')
)
const folioLanguage = {
  id: 'folio',
  scopeName: folioGrammar.scopeName,
  grammar: folioGrammar,
  aliases: ['pdf-template'],
}

const v1ArchiveSidebar = [
  {
    text: 'v1 Guide',
    items: [
      { text: 'Getting Started', link: '/guide/getting-started' },
      { text: 'Installation', link: '/guide/installation' },
      { text: 'Quick Start', link: '/guide/quick-start' },
      { text: 'Styling', link: '/guide/styling' },
      { text: 'Architecture', link: '/guide/architecture' },
    ]
  },
  {
    text: 'v1 API',
    items: [
      { text: 'Pdf', link: '/api/pdf' },
      { text: 'Nodes', link: '/api/nodes' },
      { text: 'Style', link: '/api/style' },
    ]
  },
  {
    text: 'v1 Template Language',
    items: [
      { text: 'Overview', link: '/template-language/overview' },
      { text: 'Syntax', link: '/template-language/syntax' },
      { text: 'Elements', link: '/template-language/elements' },
      { text: 'Control Flow', link: '/template-language/control-flow' },
      { text: 'Directives', link: '/template-language/directives' },
    ]
  },
  {
    text: 'v1 Tooling',
    items: [
      { text: 'Formatter', link: '/tooling/formatter' },
      { text: 'Language Server', link: '/tooling/lsp' },
      { text: 'VS Code Extension', link: '/tooling/vscode' },
      { text: 'Tree-sitter', link: '/tooling/tree-sitter' },
    ]
  },
  {
    text: 'v1 Contributing',
    items: [
      { text: 'Testing', link: '/contributing/testing' },
      { text: 'Releases', link: '/contributing/releases' },
    ]
  },
]

export default defineConfig({
  title: 'Folio',
  titleTemplate: ':title — PDF generation for PHP',
  description: 'A deliberate PDF engine for PHP 8.3+. No HTML-to-PDF wrappers, no runtime dependencies.',
  base,
  cleanUrls: true,
  lastUpdated: true,

  head: [
    ['link', { rel: 'preconnect', href: 'https://fonts.googleapis.com' }],
    ['link', { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' }],
  ],

  themeConfig: {
    logo: { src: '/logo.svg', width: 24, height: 24 },
    siteTitle: 'Folio',

    nav: [
      { text: 'Guide', link: '/v2/getting-started' },
      { text: 'Examples', link: '/v2/examples' },
      { text: 'CLI', link: '/v2/cli' },
      { text: 'Architecture', link: '/v2/architecture' },
      {
        text: 'v1.x Archive',
        items: [
          { text: 'Getting Started', link: '/guide/getting-started' },
          { text: 'Quick Start', link: '/guide/quick-start' },
          { text: 'Installation', link: '/guide/installation' },
          { text: 'API', link: '/api/pdf' },
          { text: 'Template Language', link: '/template-language/overview' },
        ],
      },
    ],

    sidebar: {
      '/v2/': [
        {
          text: 'Getting Started',
          items: [
            { text: 'Overview', link: '/v2/' },
            { text: 'Getting Started', link: '/v2/getting-started' },
            { text: 'Examples', link: '/v2/examples' },
            { text: 'Migration from v1', link: '/v2/migration' },
          ]
        },
        {
          text: 'Core Concepts',
          collapsed: false,
          items: [
            { text: 'Template Language', link: '/v2/template-language' },
            { text: 'Styling', link: '/v2/styling' },
            { text: 'CLI', link: '/v2/cli' },
            { text: 'Tooling', link: '/v2/tooling' },
          ]
        },
        {
          text: 'Advanced',
          collapsed: false,
          items: [
            { text: 'Architecture', link: '/v2/architecture' },
            { text: 'Benchmarks', link: '/v2/benchmarks' },
          ]
        },
        {
          text: 'Community',
          collapsed: false,
          items: [
            { text: 'Contributing', link: '/v2/contributing' },
          ]
        },
      ],
      '/': [
        {
          text: 'Folio 2.0',
          items: [
            { text: 'Overview', link: '/v2/' },
            { text: 'Getting Started', link: '/v2/getting-started' },
            { text: 'Template language', link: '/v2/template-language' },
            { text: 'Styling', link: '/v2/styling' },
            { text: 'Examples', link: '/v2/examples' },
            { text: 'CLI', link: '/v2/cli' },
            { text: 'Architecture', link: '/v2/architecture' },
            { text: 'Benchmarks', link: '/v2/benchmarks' },
            { text: 'Contributing', link: '/v2/contributing' },
            { text: 'Migration from v1', link: '/v2/migration' },
          ]
        },
      ],
      '/guide/': v1ArchiveSidebar,
      '/api/': v1ArchiveSidebar,
      '/template-language/': v1ArchiveSidebar,
      '/tooling/': v1ArchiveSidebar,
      '/contributing/': v1ArchiveSidebar,
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/mohammadraufzahed/folio' }
    ],

    search: {
      provider: 'local'
    },

    outline: {
      level: 'deep',
      label: 'On this page',
    },

    footer: {
      message: 'Engineered for teams that care about predictable PDF output.',
      copyright: 'Copyright © Mohammad Raufzahed. Released under the MIT License.',
    },
  },

  markdown: {
    languages: [folioGrammar],
    theme: {
      light: 'github-light',
      dark: 'github-dark',
    },
  },
})
