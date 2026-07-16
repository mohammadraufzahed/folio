import * as vscode from 'vscode';
import { FolioPdfLspClient } from './lspClient';

let lspClient: FolioPdfLspClient | undefined;

export async function activate(context: vscode.ExtensionContext): Promise<void> {
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
        }),
    );

    try {
        await lspClient.start();
    } catch (error) {
        vscode.window.showErrorMessage(`Failed to start Folio PDF LSP: ${error}`);
    }
}

export async function deactivate(): Promise<void> {
    await lspClient?.stop();
}
