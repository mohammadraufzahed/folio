import * as fs from 'fs';
import * as path from 'path';
import * as vscode from 'vscode';
import {
    LanguageClient,
    LanguageClientOptions,
    ServerOptions,
    TransportKind,
} from 'vscode-languageclient/node';

export class FolioPdfLspClient implements vscode.Disposable {
    private client: LanguageClient | null = null;
    private readonly context: vscode.ExtensionContext;

    constructor(context: vscode.ExtensionContext) {
        this.context = context;
    }

    async start(): Promise<void> {
        const config = vscode.workspace.getConfiguration('folio-pdf');
        if (!config.get('lsp.enable', true)) {
            return;
        }

        const phpPath = config.get<string>('lsp.phpPath', 'php') || 'php';
        const serverPath = this.resolveServerPath(config.get<string>('lsp.serverPath', '') || '');

        if (!serverPath || !fs.existsSync(serverPath)) {
            vscode.window.showWarningMessage(
                `Folio PDF LSP: server not found at ${serverPath || '(empty)'}. Set folio-pdf.lsp.serverPath.`
            );
            return;
        }

        const serverOptions: ServerOptions = {
            command: phpPath,
            args: [serverPath],
            transport: TransportKind.stdio,
            options: {
                cwd: path.dirname(path.dirname(serverPath)),
            },
        };

        const clientOptions: LanguageClientOptions = {
            documentSelector: [
                { scheme: 'file', language: 'folio' },
                { scheme: 'file', language: 'pdf-template' },
                { scheme: 'untitled', language: 'folio' },
            ],
            synchronize: {
                configurationSection: 'folio-pdf',
                fileEvents: vscode.workspace.createFileSystemWatcher('**/*.{folio,pdf-template}'),
            },
            outputChannelName: 'Folio PDF LSP',
        };

        this.client = new LanguageClient(
            'folioPdfLsp',
            'Folio PDF LSP',
            serverOptions,
            clientOptions
        );

        this.context.subscriptions.push(this.client);
        await this.client.start();
    }

    async restart(): Promise<void> {
        await this.stop();
        await this.start();
    }

    async stop(): Promise<void> {
        if (this.client) {
            await this.client.stop();
            this.client = null;
        }
    }

    dispose(): void {
        void this.stop();
    }

    private resolveServerPath(configured: string): string {
        if (configured) {
            return configured;
        }

        const folders = vscode.workspace.workspaceFolders;
        if (folders) {
            for (const folder of folders) {
                const root = folder.uri.fsPath;

                // Development layout: the repo itself is open.
                const devCandidate = path.join(root, 'lsp', 'lsp.php');
                if (fs.existsSync(devCandidate)) {
                    return devCandidate;
                }

                // Composer-installed package layout.
                const packageCandidates = [
                    path.join(root, 'vendor', 'mohammadraufzahed', 'folio', 'lsp', 'lsp.php'),
                    path.join(root, 'vendor', 'folio', 'pdf', 'lsp', 'lsp.php'),
                ];
                for (const candidate of packageCandidates) {
                    if (fs.existsSync(candidate)) {
                        return candidate;
                    }
                }
            }
        }

        // Fallback: relative to extension install (dev layout: vscode-extension next to lsp)
        const fromExtension = path.resolve(this.context.extensionPath, '..', 'lsp', 'lsp.php');
        if (fs.existsSync(fromExtension)) {
            return fromExtension;
        }

        return '';
    }
}
