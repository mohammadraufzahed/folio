import * as vscode from 'vscode';

export class FolioPdfFormatter implements vscode.DocumentFormattingEditProvider {
    provideDocumentFormattingEdits(
        document: vscode.TextDocument,
        options: vscode.FormattingOptions,
        _token: vscode.CancellationToken
    ): vscode.TextEdit[] {
        const config = vscode.workspace.getConfiguration('folio-pdf.format');
        if (!config.get('format.enable', true) && !config.get('enable', true)) {
            // folio-pdf.format.enable
        }

        const enabled = vscode.workspace.getConfiguration('folio-pdf').get('format.enable', true);
        if (!enabled) {
            return [];
        }

        const indentSize = vscode.workspace
            .getConfiguration('folio-pdf')
            .get<number>('format.indentSize', options.tabSize || 4);

        const text = document.getText();
        const formatted = this.formatTemplate(text, indentSize);

        if (formatted === text) {
            return [];
        }

        const fullRange = new vscode.Range(
            document.positionAt(0),
            document.positionAt(text.length)
        );

        return [vscode.TextEdit.replace(fullRange, formatted)];
    }

    formatTemplate(template: string, indentSize: number): string {
        const indentUnit = ' '.repeat(indentSize);
        let level = 0;
        const out: string[] = [];

        for (const raw of template.split('\n')) {
            const trimmed = raw.trim();
            if (!trimmed) {
                if (out.length > 0 && out[out.length - 1] !== '') {
                    out.push('');
                }
                continue;
            }

            if (trimmed.startsWith('}')) {
                level = Math.max(0, level - 1);
            }

            out.push(indentUnit.repeat(level) + trimmed);

            const opens = (trimmed.match(/\{/g) || []).length;
            const closes = (trimmed.match(/\}/g) || []).length;
            const net = opens - closes;
            if (net > 0) {
                level += net;
            }
        }

        while (out.length > 0 && out[out.length - 1] === '') {
            out.pop();
        }

        return out.join('\n') + '\n';
    }
}
