# Mermaid Diagram Policy

This repository excludes mermaid diagram files from version control to prevent:

1. **Large file bloat**: Generated images (PNG, SVG, PDF) can be large
2. **Version conflicts**: Generated files change frequently and cause merge conflicts  
3. **Build reproducibility**: Diagrams should be generated from source, not committed
4. **Security**: Prevents accidental commit of sensitive diagram content

## Excluded File Types

The following mermaid-related files are automatically ignored:

- `*.mmd` - Mermaid source files
- `*.mermaid` - Mermaid source files  
- `mermaid-*.png` - Generated PNG images
- `mermaid-*.svg` - Generated SVG images
- `mermaid-*.pdf` - Generated PDF exports
- `**/mermaid-diagrams/` - Mermaid diagram directories
- `mermaid-cache/` - Mermaid cache directory
- `.mermaid/` - Mermaid configuration directory

## Best Practices

If you need to include diagrams in the project:

1. Store mermaid source code in documentation or comments
2. Generate images as part of build process
3. Use external diagram hosting services
4. Keep mermaid source in separate documentation repository