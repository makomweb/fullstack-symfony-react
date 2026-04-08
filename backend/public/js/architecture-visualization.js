/**
 * Architecture Visualization Module
 * Renders an onion architecture diagram using SVG
 * 
 * Layers are positioned from innermost (Core) to outermost (Tests)
 * Dependency arrows point outward from a layer to its dependencies
 */

class ArchitectureVisualization {
    constructor(containerId, data) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error(`Container with id "${containerId}" not found`);
            return;
        }
        
        this.data = data;
        this.width = 900;
        this.height = 700;
        this.centerX = this.width / 2;
        this.centerY = this.height / 2;
        this.margin = 80;
        this.render();
    }

    render() {
        this.container.innerHTML = '';
        
        const layers = this.data.layers || {};
        const dependencies = this.data.dependencies || {};
        
        if (Object.keys(layers).length === 0) {
            this.container.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">No architecture data available</p>';
            return;
        }

        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('width', this.width);
        svg.setAttribute('height', this.height);
        svg.setAttribute('style', 'border: 1px solid #ddd; border-radius: 4px; background: linear-gradient(135deg, #f9f9f9 0%, #ffffff 100%);');
        
        // Define arrow markers for different angles
        this.addArrowMarkers(svg);
        
        const availableRadius = Math.min(this.width, this.height) / 2 - this.margin;
        
        // Fixed layer order: Core innermost, Tests outermost
        const layerOrder = ['Core', 'Supporting', 'Generic', 'Tests'];
        const sortedLayers = layerOrder
            .filter(name => layers[name])
            .map(name => ({ name, ...layers[name] }));
        
        const layerCount = sortedLayers.length;
        const layerThickness = availableRadius / layerCount;
        
        // Color palette - darker colors, Core is dark red
        const colors = {
            'Core': { fill: '#8B1A1A', stroke: '#5C0A0A', text: '#ffffff' },
            'Supporting': { fill: '#1B8A7E', stroke: '#0F5F57', text: '#ffffff' },
            'Generic': { fill: '#0066CC', stroke: '#003D99', text: '#ffffff' },
            'Tests': { fill: '#FF6B35', stroke: '#CC5529', text: '#ffffff' }
        };
        
        // Create position map for arrows
        const layerPositions = {};
        
        // Draw concentric rings from innermost (Core) to outermost (Tests)
        sortedLayers.forEach((layer, index) => {
            // Reverse the radius: Core should be INNERMOST (smallest radius)
            // Tests should be OUTERMOST (largest radius)
            const depth = layerCount - 1 - index;
            const innerRadius = availableRadius - ((depth + 1) * layerThickness);
            const outerRadius = availableRadius - (depth * layerThickness);
            const midRadius = (innerRadius + outerRadius) / 2;
            
            layerPositions[layer.name] = {
                index,
                innerRadius,
                outerRadius,
                midRadius
            };
            
            const color = colors[layer.name];
            
            // Draw annulus (ring) using path instead of overlapping circles
            // This prevents color blending from opacity overlap
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            
            // Create a ring path: outer circle - inner circle
            const outerCircle = `M ${this.centerX + outerRadius} ${this.centerY} A ${outerRadius} ${outerRadius} 0 1 1 ${this.centerX - outerRadius} ${this.centerY} A ${outerRadius} ${outerRadius} 0 1 1 ${this.centerX + outerRadius} ${this.centerY}`;
            const innerCircle = `M ${this.centerX + innerRadius} ${this.centerY} A ${innerRadius} ${innerRadius} 0 1 0 ${this.centerX - innerRadius} ${this.centerY} A ${innerRadius} ${innerRadius} 0 1 0 ${this.centerX + innerRadius} ${this.centerY}`;
            
            const pathData = outerCircle + ' ' + innerCircle;
            path.setAttribute('d', pathData);
            path.setAttribute('fill', color.fill);
            path.setAttribute('fill-rule', 'evenodd');
            path.setAttribute('stroke', color.stroke);
            path.setAttribute('stroke-width', '1');
            svg.appendChild(path);
            
            // Add layer name
            const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            label.setAttribute('x', this.centerX);
            label.setAttribute('y', this.centerY - midRadius);
            label.setAttribute('text-anchor', 'middle');
            label.setAttribute('dominant-baseline', 'middle');
            label.setAttribute('font-size', '18');
            label.setAttribute('font-weight', 'bold');
            label.setAttribute('fill', color.text);
            label.textContent = layer.name;
            svg.appendChild(label);
        });
        
        // Draw dependency arrows with different angles to avoid overlap
        this.drawDependencyArrows(svg, sortedLayers, dependencies, layerPositions);
        
        // Add legend
        this.addLegend(svg);
        
        this.container.appendChild(svg);
    }

    addArrowMarkers(svg) {
        const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
        
        // Create arrow marker
        const marker = document.createElementNS('http://www.w3.org/2000/svg', 'marker');
        marker.setAttribute('id', 'arrowhead');
        marker.setAttribute('markerWidth', '10');
        marker.setAttribute('markerHeight', '10');
        marker.setAttribute('refX', '8');
        marker.setAttribute('refY', '3');
        marker.setAttribute('orient', 'auto');
        
        const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
        polygon.setAttribute('points', '0 0, 10 3, 0 6');
        polygon.setAttribute('fill', '#666');
        marker.appendChild(polygon);
        defs.appendChild(marker);
        
        svg.appendChild(defs);
    }

    drawDependencyArrows(svg, sortedLayers, dependencies, layerPositions) {
        // For each layer, draw arrows to its dependencies
        sortedLayers.forEach((sourceLayer) => {
            const deps = dependencies[sourceLayer.name] || [];
            const sourcePos = layerPositions[sourceLayer.name];
            
            if (!deps || deps.length === 0) return;
            
            // Spread arrows around the circle to avoid overlap
            const startAngle = -90; // Start at top
            const angleStep = 360 / (deps.length + 1);
            
            deps.forEach((targetLayerName, depIndex) => {
                const targetPos = layerPositions[targetLayerName];
                if (!targetPos) return;
                
                // Calculate angle for this arrow
                const angle = startAngle + (depIndex + 1) * angleStep;
                const angleRad = (angle * Math.PI) / 180;
                
                // Start point: at outer edge of source layer
                const startRadius = sourcePos.outerRadius + 5;
                const startX = this.centerX + Math.cos(angleRad) * startRadius;
                const startY = this.centerY + Math.sin(angleRad) * startRadius;
                
                // End point: at outer edge of target layer
                const endRadius = targetPos.outerRadius + 5;
                const endX = this.centerX + Math.cos(angleRad) * endRadius;
                const endY = this.centerY + Math.sin(angleRad) * endRadius;
                
                // Draw curved arrow (quadratic bezier)
                const controlRadius = Math.max(startRadius, endRadius) + 100;
                const controlX = this.centerX + Math.cos(angleRad) * controlRadius;
                const controlY = this.centerY + Math.sin(angleRad) * controlRadius;
                
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                const pathData = `M ${startX} ${startY} Q ${controlX} ${controlY} ${endX} ${endY}`;
                path.setAttribute('d', pathData);
                path.setAttribute('fill', 'none');
                path.setAttribute('stroke', '#999');
                path.setAttribute('stroke-width', '2');
                path.setAttribute('stroke-dasharray', '5,5');
                path.setAttribute('opacity', '0.6');
                path.setAttribute('marker-end', 'url(#arrowhead)');
                svg.appendChild(path);
            });
        });
    }

    addLegend(svg) {
        const legendX = this.width - 200;
        const legendY = 20;
        
        // Color mapping
        const colorMap = {
            'Core': '#8B1A1A',
            'Supporting': '#1B8A7E',
            'Generic': '#0066CC',
            'Tests': '#FF6B35'
        };
        
        // Legend background
        const bg = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        bg.setAttribute('x', legendX - 10);
        bg.setAttribute('y', legendY - 10);
        bg.setAttribute('width', 190);
        bg.setAttribute('height', 150);
        bg.setAttribute('fill', '#ffffff');
        bg.setAttribute('stroke', '#ddd');
        bg.setAttribute('stroke-width', '1');
        bg.setAttribute('opacity', '0.95');
        svg.appendChild(bg);
        
        // Legend title
        const title = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        title.setAttribute('x', legendX);
        title.setAttribute('y', legendY + 15);
        title.setAttribute('font-size', '14');
        title.setAttribute('font-weight', 'bold');
        title.setAttribute('fill', '#333');
        title.textContent = 'Layers';
        svg.appendChild(title);
        
        // Legend items
        const layers = ['Core', 'Supporting', 'Generic', 'Tests'];
        layers.forEach((layerName, i) => {
            const yPos = legendY + 35 + i * 25;
            
            // Colored square
            const square = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            square.setAttribute('x', legendX);
            square.setAttribute('y', yPos - 8);
            square.setAttribute('width', '12');
            square.setAttribute('height', '12');
            square.setAttribute('fill', colorMap[layerName]);
            svg.appendChild(square);
            
            // Label
            const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            label.setAttribute('x', legendX + 20);
            label.setAttribute('y', yPos);
            label.setAttribute('font-size', '12');
            label.setAttribute('fill', '#333');
            label.textContent = layerName;
            svg.appendChild(label);
        });
        
        // Arrow legend
        const arrowY = legendY + 130;
        const arrowLine = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        arrowLine.setAttribute('d', `M ${legendX} ${arrowY} L ${legendX + 15} ${arrowY}`);
        arrowLine.setAttribute('stroke', '#999');
        arrowLine.setAttribute('stroke-width', '2');
        arrowLine.setAttribute('stroke-dasharray', '5,5');
        arrowLine.setAttribute('marker-end', 'url(#arrowhead)');
        svg.appendChild(arrowLine);
        
        const arrowLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        arrowLabel.setAttribute('x', legendX + 20);
        arrowLabel.setAttribute('y', arrowY + 3);
        arrowLabel.setAttribute('font-size', '11');
        arrowLabel.setAttribute('fill', '#666');
        arrowLabel.textContent = 'Dependency';
        svg.appendChild(arrowLabel);
    }
}

// Initialize on document ready
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('architecture-visualization');
    if (container && window.architectureData) {
        new ArchitectureVisualization('architecture-visualization', window.architectureData);
    }
});
