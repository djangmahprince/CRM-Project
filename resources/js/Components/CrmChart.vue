<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import { Chart, DoughnutController, ArcElement, BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js';

Chart.register(DoughnutController, ArcElement, BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

const props = defineProps({
    type: {
        type: String,
        default: 'bar',
    },
    labels: {
        type: Array,
        default: () => [],
    },
    values: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        default: 'Value',
    },
});

const canvas = ref(null);
let chart;

function render() {
    if (!canvas.value) {
        return;
    }

    chart?.destroy();
    chart = new Chart(canvas.value, {
        type: props.type,
        data: {
            labels: props.labels,
            datasets: [{
                label: props.label,
                data: props.values,
                backgroundColor: [
                    '#2447b9',
                    '#3b63d0',
                    '#5b7dff',
                    '#8aa0ea',
                    '#d97706',
                    '#94a3b8',
                    '#1b3694',
                    '#cbd5e1',
                ],
                borderWidth: 0,
                borderRadius: props.type === 'bar' ? 4 : 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: props.type === 'doughnut',
                    position: 'bottom',
                },
            },
            scales: props.type === 'bar' ? {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: '#e2e8f0' } },
            } : undefined,
        },
    });
}

onMounted(render);
watch(() => [props.labels, props.values, props.type], render, { deep: true });
onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <div class="relative h-64 w-full">
        <canvas ref="canvas" aria-label="Chart" role="img" />
    </div>
</template>
