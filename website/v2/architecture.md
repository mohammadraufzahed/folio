# Architecture

Folio 2.0 follows an onion / hexagonal architecture. Dependencies always point
inward.

## Layers

| Layer | Responsibility |
|-------|----------------|
| **Domain** | `Node`, `Style`, `Layout`, `Pagination`, `StyleEngine` |
| **Application** | `TemplateEngine`, `BuildDocument`, `ExportToFile` |
| **Ports** | `RendererPort`, `FontMetricsPort`, `ImageResolverPort`, `CachePort` |
| **Infrastructure** | `Pdf1_7Renderer`, `Core14FontMetrics`, file cache |

## Ports

```php
interface RendererPort
{
    public function render(Document $document, LayoutResult $layout): string;
}
```

The domain never depends on PDF bytes, file systems or network calls. All
infrastructure is injected through adapters.
