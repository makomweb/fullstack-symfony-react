# Deptrac Architecture Visualization

A pure JavaScript library for visualizing onion/hexagonal architecture diagrams from deptrac.yaml configurations.

## Usage

```javascript
const viz = new DeptracVisualization('container-id', {
    layers: {
        Core: { /* layer data */ },
        Supporting: { /* layer data */ },
        // ...
    },
    dependencies: {
        Core: [],
        Supporting: ['Core', 'Generic'],
        // ...
    }
});
```

## Features

- Pure SVG rendering (no external dependencies)
- Configurable layer order and colors
- Automatic dependency arrow layout
- Legend with layer information
- Responsive design with viewBox support

## Configuration

The visualization accepts data with two properties:

- **layers**: Object mapping layer names to layer definitions
- **dependencies**: Object mapping layer names to arrays of their dependencies

## Colors

Default color scheme:
- Core: Dark Red (#8B1A1A)
- Supporting: Dark Teal (#1B8A7E)
- Generic: Dark Blue (#0066CC)
- Tests: Orange (#FF6B35)

## Architecture

The visualization represents a semantic onion architecture:
1. **Core** (innermost) - Business logic, no dependencies
2. **Supporting** - Adapters and infrastructure code
3. **Tests** - Application tests
4. **Generic** (outermost) - External frameworks and libraries

## Notes

This module is designed for extraction as a standalone npm package while being integrated into the current project.
