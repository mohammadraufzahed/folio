import * as vscode from 'vscode';
import { spawn } from 'child_process';
import { FolioPdfFormatter } from './formatter';
import { FolioPdfLspClient } from './lspClient';

let lspClient: FolioPdfLspClient | undefined;

export async function activate(context: vscode.ExtensionContext): Promise<void> {
    const formatter = new FolioPdfFormatter();

    context.subscriptions.push(
        vscode.languages.registerDocumentFormattingEditProvider(
            [
                { language: 'folio', scheme: 'file' },
                { language: 'folio', scheme: 'untitled' },
                { language: 'pdf-template', scheme: 'file' },
                { language: 'pdf-template', scheme: 'untitled' },
            ],
            formatter
        )
    );

    context.subscriptions.push(
        vscode.commands.registerCommand('folio-pdf.formatDocument', async () => {
            const editor = vscode.window.activeTextEditor;
            if (!editor) {
                vscode.window.showWarningMessage('No active editor');
                return;
            }

            const edits = formatter.provideDocumentFormattingEdits(
                editor.document,
                {
                    tabSize: (editor.options.tabSize as number) || 4,
                    insertSpaces: (editor.options.insertSpaces as boolean) ?? true,
                },
                new vscode.CancellationTokenSource().token
            );

            if (edits.length === 0) {
                vscode.window.showInformationMessage('Document already formatted');
                return;
            }

            const workspaceEdit = new vscode.WorkspaceEdit();
            workspaceEdit.set(editor.document.uri, edits);
            await vscode.workspace.applyEdit(workspaceEdit);
        })
    );

    context.subscriptions.push(
        vscode.commands.registerCommand('folio-pdf.compileTemplate', async () => {
            const editor = vscode.window.activeTextEditor;
            if (!editor) {
                return;
            }

            const text = editor.document.getText();
            const workspaceFolder = vscode.workspace.getWorkspaceFolder(editor.document.uri);
            const root = workspaceFolder?.uri.fsPath;

            if (!root) {
                vscode.window.showErrorMessage('Open a workspace folder to compile templates.');
                return;
            }

            const php =
                vscode.workspace.getConfiguration('folio-pdf').get<string>('lsp.phpPath', 'php') ||
                'php';

            try {
                const stdout = await runPhpCompile(php, root, text);
                const doc = await vscode.workspace.openTextDocument({
                    content: stdout,
                    language: 'php',
                });
                await vscode.window.showTextDocument(doc, vscode.ViewColumn.Beside);
                vscode.window.showInformationMessage('Template compiled successfully');
            } catch (error) {
                vscode.window.showErrorMessage(`Compile failed: ${error}`);
            }
        })
    );

    lspClient = new FolioPdfLspClient(context);
    context.subscriptions.push(lspClient);

    context.subscriptions.push(
        vscode.commands.registerCommand('folio-pdf.restartLsp', async () => {
            try {
                await lspClient?.restart();
                vscode.window.showInformationMessage('Folio PDF language server restarted');
            } catch (error) {
                vscode.window.showErrorMessage(`Failed to restart LSP: ${error}`);
            }
        })
    );

    try {
        await lspClient.start();
    } catch (error) {
        vscode.window.showErrorMessage(`Failed to start Folio PDF LSP: ${error}`);
    }
}

function runPhpCompile(php: string, root: string, template: string): Promise<string> {
    return new Promise((resolve, reject) => {
        const script = [
            `require '${root.replace(/'/g, "\\'")}/vendor/autoload.php';`,
            'use Folio\\Pdf\\Template\\PhpTemplateCompiler;',
            '$c = new PhpTemplateCompiler();',
            'echo $c->compile(stream_get_contents(STDIN));',
        ].join('');

        const child = spawn(php, ['-r', script], { cwd: root });
        let stdout = '';
        let stderr = '';

        child.stdout.on('data', (chunk: Buffer) => {
            stdout += chunk.toString();
        });
        child.stderr.on('data', (chunk: Buffer) => {
            stderr += chunk.toString();
        });
        child.on('error', reject);
        child.on('close', (code) => {
            if (code === 0) {
                resolve(stdout);
            } else {
                reject(new Error(stderr || `php exited with code ${code}`));
            }
        });

        child.stdin.write(template);
        child.stdin.end();
    });
}

export async function deactivate(): Promise<void> {
    await lspClient?.stop();
}
