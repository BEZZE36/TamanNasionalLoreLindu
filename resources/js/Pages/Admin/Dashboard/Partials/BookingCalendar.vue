<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { Calendar, ChevronLeft, ChevronRight, Eye } from 'lucide-vue-next';

const props = defineProps({
    calendarBookings: Object,
});

let ctx;

// Calendar state
const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());

const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

// Get days in month
const getDaysInMonth = (year, month) => new Date(year, month + 1, 0).getDate();
const getFirstDayOfMonth = (year, month) => new Date(year, month, 1).getDay();

// Generate calendar days
const calendarDays = computed(() => {
    const days = [];
    const daysInMonth = getDaysInMonth(currentYear.value, currentMonth.value);
    const firstDay = getFirstDayOfMonth(currentYear.value, currentMonth.value);
    
    // Previous month days
    const prevMonthDays = getDaysInMonth(currentYear.value, currentMonth.value - 1);
    for (let i = firstDay - 1; i >= 0; i--) {
        days.push({ day: prevMonthDays - i, isCurrentMonth: false, bookings: 0 });
    }
    
    // Current month days
    for (let i = 1; i <= daysInMonth; i++) {
        const dateStr = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
        const bookingCount = props.calendarBookings?.[dateStr] || 0;
        days.push({ 
            day: i, 
            isCurrentMonth: true, 
            bookings: bookingCount,
            date: dateStr,
            isToday: new Date().toISOString().split('T')[0] === dateStr
        });
    }
    
    // Next month days
    const remainingDays = 42 - days.length;
    for (let i = 1; i <= remainingDays; i++) {
        days.push({ day: i, isCurrentMonth: false, bookings: 0 });
    }
    
    return days;
});

const prevMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
};

const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
};

const totalBookingsThisMonth = computed(() => {
    return Object.values(props.calendarBookings || {}).reduce((a, b) => a + b, 0);
});

onMounted(() => {
    nextTick(() => {
        const days = document.querySelectorAll('.calendar-day');
        if (days.length > 0) {
            ctx = gsap.context(() => {
                gsap.fromTo(days, 
                    { opacity: 0, scale: 0.8 }, 
                    { opacity: 1, scale: 1, duration: 0.3, stagger: 0.01, ease: 'back.out(1.5)' }
                );
            });
        }
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden h-full">
        <!-- Header -->
        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-violet-50 to-purple-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/30">
                        <Calendar class="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Kalender Booking</h3>
                        <p class="text-[10px] text-gray-500">{{ totalBookingsThisMonth }} booking bulan ini</p>
                    </div>
                </div>
            </div>
            
            <!-- Month Navigation -->
            <div class="flex items-center justify-between mt-3">
                <button 
                    @click="prevMonth"
                    class="p-1.5 rounded-lg hover:bg-white/80 text-gray-500 hover:text-violet-600 transition-all"
                >
                    <ChevronLeft class="w-4 h-4" />
                </button>
                <span class="text-xs font-bold text-gray-700">{{ monthNames[currentMonth] }} {{ currentYear }}</span>
                <button 
                    @click="nextMonth"
                    class="p-1.5 rounded-lg hover:bg-white/80 text-gray-500 hover:text-violet-600 transition-all"
                >
                    <ChevronRight class="w-4 h-4" />
                </button>
            </div>
        </div>
        
        <!-- Calendar Grid -->
        <div class="p-3">
            <!-- Day Names -->
            <div class="grid grid-cols-7 gap-1 mb-2">
                <div 
                    v-for="day in dayNames" 
                    :key="day" 
                    class="text-center text-[9px] font-bold text-gray-400 uppercase"
                >
                    {{ day }}
                </div>
            </div>
            
            <!-- Days Grid -->
            <div class="grid grid-cols-7 gap-1">
                <Link 
                    v-for="(day, index) in calendarDays" 
                    :key="index"
                    :href="day.isCurrentMonth && day.bookings > 0 ? `/admin/bookings?date=${day.date}` : '#'"
                    :class="[
                        'calendar-day relative flex flex-col items-center justify-center p-1.5 rounded-lg text-[10px] transition-all duration-200',
                        day.isCurrentMonth 
                            ? day.isToday 
                                ? 'bg-gradient-to-br from-violet-500 to-purple-600 text-white font-bold shadow-lg' 
                                : day.bookings > 0 
                                    ? 'bg-emerald-50 text-emerald-700 font-semibold hover:bg-emerald-100 cursor-pointer' 
                                    : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
                            : 'text-gray-300'
                    ]"
                >
                    <span>{{ day.day }}</span>
                    <!-- Booking indicator -->
                    <div 
                        v-if="day.isCurrentMonth && day.bookings > 0 && !day.isToday" 
                        class="absolute -bottom-0.5 w-1 h-1 rounded-full bg-emerald-500"
                    ></div>
                    <!-- Booking count tooltip -->
                    <span 
                        v-if="day.isCurrentMonth && day.bookings > 0" 
                        :class="[
                            'absolute -top-1 -right-1 min-w-[14px] h-[14px] flex items-center justify-center rounded-full text-[8px] font-bold',
                            day.isToday ? 'bg-white text-violet-600' : 'bg-emerald-500 text-white'
                        ]"
                    >
                        {{ day.bookings }}
                    </span>
                </Link>
            </div>
        </div>
        
        <!-- Legend -->
        <div class="px-3 pb-3 flex items-center gap-3 text-[9px] text-gray-500">
            <div class="flex items-center gap-1">
                <div class="w-2 h-2 rounded-full bg-gradient-to-br from-violet-500 to-purple-600"></div>
                <span>Hari ini</span>
            </div>
            <div class="flex items-center gap-1">
                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                <span>Ada booking</span>
            </div>
        </div>
    </div>
</template>
