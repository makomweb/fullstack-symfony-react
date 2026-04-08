/**
 * Deptrac Architecture Visualization
 * Renders an onion architecture diagram from deptrac.yaml using SVG
 * 
 * Usage:
 *   const viz = new DeptracVisualization('container-id', {
 *     layers: { Core: {...}, Supporting: {...}, ... },
 *     dependencies: { Core: [], Supporting: ['Core'], ... }
 *   });
 */

class DeptracVisualization {
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

        const svg = this._createSvg();
        const availableRadius = Math.min(this.width, this.height) / 2 - this.margin;
        
        const layerOrder = ['Core', 'Supporting', 'Tests', 'Generic'];
        const sortedLayers = layerOrder
            .filter(name => layers[name])
            .map(name => ({ name, ...layers[name] }));
        
        const layerCount = sortedLayers.length;
        const layerThickness = availableRadius / layerCount;
        const colors = this._getColorPalette();
        const layerPositions = {};
        
        sortedLayers.forEach((layer, index) => {
            const depth = layerCount - 1 - index;
            const innerRadius = availableRadius - ((depth + 1) * layerThickness);
            const outerRadius = availableRadius - (depth * layerThickness);
            const midRadius = (innerRadius + outerRadius) / 2;
            
            layerPositions[layer.name] = { index, innerRadius, outerRadius, midRadius };
            
            this._drawLayer(svg, layer, colors[layer.name], innerRadius, outerRadius, midRadius);
        });
        
        this._drawDependencyArrows(svg, sortedLayers, dependencies, layerPositions);
        this._drawLegend(svg, colors);
        
        this.container.appendChild(svg);
    }

    _createSvg() {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('width', this.width);
        svg.setAttribute('height', this.height);
        svg.setAttribute('style', 'display: block;');
        svg.setAttribute('viewBox', `0 0 ${this.width} ${this.height}`);
        
        const bg = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        bg.setAttribute('width', this.width);
        bg.setAttribute('height', this.height);
        bg.setAttribute('fill', 'white');
        svg.appendChild(bg);
        
        this._addArrowMarker(svg);
        return svg;
    }

    _getColorPalette() {
        return {
            'Core': { fill: '#ff00eecc', stroke: '#9E7777', text: '#ffffff' },
            'Supporting': { fill: '#ff00ee99', stroke: '#5F9A8C', text: '#ffffff' },
            'Generic': { fill: '#ff00ee33', stroke: '#4E7FA3', text: '#ffffff' },
            'Tests': { fill: '#ff00ee77', stroke: '#D08842', text: '#ffffff' }
        };
    }

    _addArrowMarker(svg) {
        const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
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

    _drawLayer(svg, layer, color, innerRadius, outerRadius, midRadius) {
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        const outerCircle = `M ${this.centerX + outerRadius} ${this.centerY} A ${outerRadius} ${outerRadius} 0 1 1 ${this.centerX - outerRadius} ${this.centerY} A ${outerRadius} ${outerRadius} 0 1 1 ${this.centerX + outerRadius} ${this.centerY}`;
        const innerCircle = `M ${this.centerX + innerRadius} ${this.centerY} A ${innerRadius} ${innerRadius} 0 1 0 ${this.centerX - innerRadius} ${this.centerY} A ${innerRadius} ${innerRadius} 0 1 0 ${this.centerX + innerRadius} ${this.centerY}`;
        
        path.setAttribute('d', outerCircle + ' ' + innerCircle);
        path.setAttribute('fill', color.fill);
        path.setAttribute('fill-rule', 'evenodd');
        path.setAttribute('stroke', color.stroke);
        path.setAttribute('stroke-width', '1');
        svg.appendChild(path);
        
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
    }

    _drawDependencyArrows(svg, sortedLayers, dependencies, layerPositions) {
        sortedLayers.forEach((sourceLayer) => {
            const deps = dependencies[sourceLayer.name] || [];
            const sourcePos = layerPositions[sourceLayer.name];
            
            if (!deps || deps.length === 0) return;
            
            deps.forEach((targetLayerName) => {
                const targetPos = layerPositions[targetLayerName];
                if (!targetPos) return;
                
                const depCount = deps.length;
                const depIndex = deps.indexOf(targetLayerName);
                const angle = (depIndex * (360 / depCount)) - 90 + 22.5;
                const angleRad = (angle * Math.PI) / 180;
                
                const startX = this.centerX + Math.cos(angleRad) * sourcePos.midRadius;
                const startY = this.centerY + Math.sin(angleRad) * sourcePos.midRadius;
                const endX = this.centerX + Math.cos(angleRad) * targetPos.midRadius;
                const endY = this.centerY + Math.sin(angleRad) * targetPos.midRadius;
                
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', `M ${startX} ${startY} L ${endX} ${endY}`);
                path.setAttribute('fill', 'none');
                path.setAttribute('stroke', '#333');
                path.setAttribute('stroke-width', '2');
                path.setAttribute('marker-end', 'url(#arrowhead)');
                svg.appendChild(path);
            });
        });
    }

    _drawLegend(svg, colors) {
        const legendX = this.width - 200;
        const legendY = 20;
        
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
        
        const title = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        title.setAttribute('x', legendX);
        title.setAttribute('y', legendY + 15);
        title.setAttribute('font-size', '14');
        title.setAttribute('font-weight', 'bold');
        title.setAttribute('fill', '#333');
        title.textContent = 'Layers';
        svg.appendChild(title);
        
        ['Core', 'Supporting', 'Generic', 'Tests'].forEach((layerName, i) => {
            const yPos = legendY + 35 + i * 25;
            
            const square = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            square.setAttribute('x', legendX);
            square.setAttribute('y', yPos - 8);
            square.setAttribute('width', '12');
            square.setAttribute('height', '12');
            square.setAttribute('fill', colors[layerName].fill);
            svg.appendChild(square);
            
            const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            label.setAttribute('x', legendX + 20);
            label.setAttribute('y', yPos);
            label.setAttribute('font-size', '12');
            label.setAttribute('fill', '#333');
            label.textContent = layerName;
            svg.appendChild(label);
        });
        
        const arrowY = legendY + 130;
        const arrowLine = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        arrowLine.setAttribute('d', `M ${legendX} ${arrowY} L ${legendX + 15} ${arrowY}`);
        arrowLine.setAttribute('stroke', '#333');
        arrowLine.setAttribute('stroke-width', '2');
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

// For backward compatibility
const ArchitectureVisualization = DeptracVisualization;

// Initialize on document ready
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('architecture-visualization');
    if (container && window.architectureData) {
        new DeptracVisualization('architecture-visualization', window.architectureData);
    }
});
