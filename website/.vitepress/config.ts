import { defineConfig } from 'vitepress'

const base = process.env.VITEPRESS_BASE || '/folio/'

export default defineConfig({
  title: 'Folio PDF',
  description: 'Composable PDF generation for PHP 8.3+',
  base,

  themeConfig: {
    nav: [
      { text: 'Guide', link: '/guide/getting-started' },
      { text: 'API', link: '/api/pdf' },
      { text: 'Templates', link: '/template-language/overview' },
      { text: 'Tooling', link: '/tooling/formatter' },
    ],

    sidebar: {
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
          ]
        },
      ],
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/mohammadraufzahed/folio' }
    ],

    search: {
      provider: 'local'
    }
  }
})
