<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { gsap } from 'gsap';
import { TrendingUp, Users, DollarSign, Eye, ChevronDown } from 'lucide-vue-next';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    labels: Array,
    revenueData: Array,
    visitorData: Array,
    periodLabel: String,
});

const chartRef = ref(null);
const activeTab = ref('revenue');
let chartInstance = null;
let ctx;

const formatCurrency = (value) => {
    if (value >= 1000000) return `${(value / 1000000).toFixed(1)}jt`;
    if (value >= 1000) return `${(value / 1000).toFixed(0)}rb`;
    return value.toString();
};

const createChart = () => {
    if (chartInstance) {
        chartInstance.destroy();
    }
    
    if (!chartRef.value) return;
    
    const ctx = chartRef.value.getContext('2d');
    
    const isRevenue = activeTab.value === 'revenue';
    const data = isRevenue ? props.revenueData : props.visitorData;
    
    // Create gradient
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    if (isRevenue) {
        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
        gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');
    } else {
        gradient.addColorStop(0, 'rgba(139, 92, 246, 0.4)');
        gradient.addColorStop(1, 'rgba(139, 92, 246, 0.02)');
    }
    
    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: props.labels || [],
            datasets: [{
                label: isRevenue ? 'Pendapatan' : 'Pengunjung',
                data: data || [],
                fill: true,
                backgroundColor: gradient,
                borderColor: isRevenue ? 'rgb(16, 185, 129)' : 'rgb(139, 92, 246)',
                borderWidth: 2.5,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: isRevenue ? 'rgb(16, 185, 129)' : 'rgb(139, 92, 246)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: isRevenue ? 'rgb(16, 185, 129)' : 'rgb(139, 92, 246)',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        font: { size: 10, weight: '500' },
                        color: '#94a3b8',
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)',
                    },
                    ticks: {
                        font: { size: 10, weight: '500' },
                        color: '#94a3b8',
                        callback: (value) => isRevenue ? formatCurrency(value) : value,
                    }
                }
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleFont: { size: 12, weight: '600' },
                    bodyFont: { size: 11 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: (context) => {
                            const value = context.parsed.y;
                            return isRevenue 
                                ? `Rp ${new Intl.NumberFormat('id-ID').format(value)}`
                                : `${value} pengunjung`;
                        }
                    }
                }
            }
        }
    });
};

onMounted(() => {
    nextTick(() => {
        createChart();
        
        const chartCard = document.querySelector('.chart-card');
        if (chartCard) {
            ctx = gsap.context(() => {
                gsap.fromTo(chartCard, 
                    { opacity: 0, y: 20 }, 
                    { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' }
                );
            });
        }
    });
});

onBeforeUnmount(() => {
    if (chartInstance) chartInstance.destroy();
    if (ctx) ctx.revert();
});

watch(activeTab, () => {
    nextTick(() => createChart());
});

watch(() => [props.revenueData, props.visitorData], () => {
    nextTick(() => createChart());
}, { deep: true });

const totalRevenue = () => {
    return props.revenueData?.reduce((a, b) => a + b, 0) || 0;
};

const totalVisitors = () => {
    return props.visitorData?.reduce((a, b) => a + b, 0) || 0;
};
</script>

<template>
    <div class="chart-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div :class="[
                        'w-10 h-10 rounded-xl flex items-center justify-center shadow-lg transition-all duration-300',
                        activeTab === 'revenue' 
                            ? 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-emerald-500/30' 
                            : 'bg-gradient-to-br from-violet-500 to-purple-600 shadow-violet-500/30'
                    ]">
                        <TrendingUp class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Grafik Trend</h3>
                        <p class="text-[10px] text-gray-500">{{ periodLabel }}</p>
                    </div>
                </div>
                
                <!-- Tabs Toggle -->
                <div class="flex items-center gap-1 p-1 bg-gray-100 rounded-lg">
                    <button 
                        @click="activeTab = 'revenue'"
                        :class="[
                            'flex items-center gap-1.5 px-3 py-1.5 rounded-md text-[11px] font-semibold transition-all duration-300',
                            activeTab === 'revenue' 
                                ? 'bg-white text-emerald-600 shadow-sm' 
                                : 'text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        <DollarSign class="w-3.5 h-3.5" />
                        Pendapatan
                    </button>
                    <button 
                        @click="activeTab = 'visitor'"
                        :class="[
                            'flex items-center gap-1.5 px-3 py-1.5 rounded-md text-[11px] font-semibold transition-all duration-300',
                            activeTab === 'visitor' 
                                ? 'bg-white text-violet-600 shadow-sm' 
                                : 'text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        <Eye class="w-3.5 h-3.5" />
                        Pengunjung
                    </button>
                </div>
            </div>
            
            <!-- Summary Stats -->
            <div class="mt-4 flex items-center gap-4">
                <div :class="[
                    'px-4 py-2 rounded-lg transition-all duration-300',
                    activeTab === 'revenue' ? 'bg-emerald-50' : 'bg-violet-50'
                ]">
                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Total Periode</p>
                    <p :class="[
                        'text-lg font-black',
                        activeTab === 'revenue' ? 'text-emerald-600' : 'text-violet-600'
                    ]">
                        {{ activeTab === 'revenue' 
                            ? `Rp ${new Intl.NumberFormat('id-ID').format(totalRevenue())}` 
                            : `${new Intl.NumberFormat('id-ID').format(totalVisitors())} ` 
                        }}
                        <span v-if="activeTab === 'visitor'" class="text-xs font-normal text-gray-500">pengunjung</span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Chart -->
        <div class="p-4">
            <div class="h-64">
                <canvas ref="chartRef"></canvas>
            </div>
        </div>
    </div>
</template>
