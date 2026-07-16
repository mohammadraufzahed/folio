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
      { text: 'Guide', link: '/guide/getting-started' },
      { text: 'API', link: '/api/pdf' },
      { text: 'Templates', link: '/template-language/overview' },
      { text: 'Tooling', link: '/tooling/formatter' },
      {
        text: 'v2.0',
        items: [
          { text: 'Folio 2.0', link: '/v2/' },
          { text: 'Architecture', link: '/v2/architecture' },
          { text: 'Template language', link: '/v2/template-language' },
          { text: 'CLI', link: '/v2/cli' },
          { text: 'Benchmarks', link: '/v2/benchmarks' },
        ],
      },
    ],

    sidebar: {
      '/v2/': [
        {
          text: 'Folio 2.0',
          items: [
            { text: 'Overview', link: '/v2/' },
            { text: 'Architecture', link: '/v2/architecture' },
            { text: 'Template language', link: '/v2/template-language' },
            { text: 'CLI', link: '/v2/cli' },
            { text: 'Benchmarks', link: '/v2/benchmarks' },
          ]
        }
      ],
      '/': [
        {
          text: 'Guide',
          items: [
            { text: 'Getting Started', link: '/guide/getting-started' },
            { text: 'Installation', link: '/guide/installation' },
            { text: 'Quick Start', link: '/guide/quick-start' },
            { text: 'Styling', link: '/guide/styling' },
            { text: 'Architecture', link: '/guide/architecture' },
          ]
        },
        {
          text: 'API',
          items: [
            { text: 'Pdf', link: '/api/pdf' },
            { text: 'Nodes', link: '/api/nodes' },
            { text: 'Style', link: '/api/style' },
          ]
        },
        {
          text: 'Template Language',
          items: [
            { text: 'Overview', link: '/template-language/overview' },
            { text: 'Syntax', link: '/template-language/syntax' },
            { text: 'Elements', link: '/template-language/elements' },
            { text: 'Control Flow', link: '/template-language/control-flow' },
            { text: 'Directives', link: '/template-language/directives' },
          ]
        },
        {
          text: 'Tooling',
          items: [
            { text: 'Formatter', link: '/tooling/formatter' },
            { text: 'Language Server', link: '/tooling/lsp' },
            { text: 'VS Code Extension', link: '/tooling/vscode' },
            { text: 'Tree-sitter', link: '/tooling/tree-sitter' },
          ]
        },
        {
          text: 'Contributing',
          items: [
            { text: 'Testing', link: '/contributing/testing' },
            { text: 'Releases', link: '/contributing/releases' },
          ]
        },
      ],
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
