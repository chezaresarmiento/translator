<template>
    <svg width="90" height="60" viewBox="0 0 100 60" xmlns="http://www.w3.org/2000/svg">
                        <!-- Left Speech Bubble -->
                        <rect x="5" y="10" width="40" height="30" rx="5" ry="5" fill="#FF2D20"/>
                        <polygon points="20,40 30,40 25,50" fill="#FF2D20"/>
                        
                        <!-- Right Speech Bubble -->
                        <rect x="55" y="10" width="40" height="30" rx="5" ry="5" fill="#FF2D20"/>
                        <polygon points="80,40 70,40 75,50" fill="#FF2D20"/>
                        
                        <!-- Curved Arrow to Indicate Translation -->
                        <path d="M45,25 C48,10 52,10 55,25" stroke="white" stroke-width="2" fill="none" marker-end="url(#arrowhead)"/>
                        
                        <defs>
                            <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="0" refY="3.5" orient="auto">
                            <polygon points="0 0, 10 3.5, 0 7" fill="white" />
                            </marker>
                        </defs>
    </svg>
</template>
