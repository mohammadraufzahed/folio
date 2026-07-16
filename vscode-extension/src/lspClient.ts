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

        const serverModule = this.context.asAbsolutePath(path.join('out', 'server', 'server.js'));

        const serverOptions: ServerOptions = {
            run: { module: serverModule, transport: TransportKind.ipc },
            debug: { module: serverModule, transport: TransportKind.ipc },
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
            clientOptions,
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
}
