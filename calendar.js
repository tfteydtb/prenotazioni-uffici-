// js/calendar.js

// This file handles calendar rendering and updates
const Calendar = {
    currentDate: new Date(),
    resources: [],
    bookings: [],
    pollInterval: 5000,
    intervalId: null,

    init(resourcesData) {
        this.resources = resourcesData;
        this.renderCalendarFrame();
        this.fetchBookings();
        this.startPolling();
        
        // Setup date navigation
        document.getElementById('prev-day').addEventListener('click', () => {
            this.currentDate.setDate(this.currentDate.getDate() - 1);
            this.updateDateDisplay();
            this.fetchBookings();
        });
        
        document.getElementById('next-day').addEventListener('click', () => {
            this.currentDate.setDate(this.currentDate.getDate() + 1);
            this.updateDateDisplay();
            this.fetchBookings();
        });
        
        document.getElementById('today').addEventListener('click', () => {
            this.currentDate = new Date();
            this.updateDateDisplay();
            this.fetchBookings();
        });
        
        this.updateDateDisplay();
    },

    updateDateDisplay() {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date-display').textContent = this.currentDate.toLocaleDateString('it-IT', options);
    },

    formatDateForApi(date) {
        const d = new Date(date);
        return d.toISOString().split('T')[0];
    },

    renderCalendarFrame() {
        const grid = document.getElementById('calendar-grid');
        grid.innerHTML = '';
        
        // Header Row (Empty corner + Hours)
        grid.appendChild(this.createCell('corner header', ''));
        for(let h=8; h<18; h++) {
            grid.appendChild(this.createCell('header', `${h}:00`));
        }
        
        // Resource Rows
        this.resources.forEach(res => {
            // Resource Label
            grid.appendChild(this.createCell('header', `${res.nome}<br><small>${res.tipo}</small>`));
            
            // Hour slots
            for(let h=8; h<18; h++) {
                const cell = this.createCell('calendar-cell time-label', '');
                cell.dataset.resourceId = res.id;
                cell.dataset.hour = h;
                
                // Add click listener for booking
                cell.addEventListener('click', () => this.handleSlotClick(res, h));
                
                grid.appendChild(cell);
            }
        });
    },

    createCell(className, innerHTML) {
        const div = document.createElement('div');
        div.className = className;
        div.innerHTML = innerHTML;
        return div;
    },

    async fetchBookings() {
        try {
            const dateStr = this.formatDateForApi(this.currentDate);
            const response = await fetch(`api/get_bookings.php?date=${dateStr}`);
            
            if (!response.ok) throw new Error('Network error');
            
            const data = await response.json();
            
            // Check for concurrent updates that might affect user
            if (this.bookings.length > 0) {
                this.checkChanges(this.bookings, data);
            }
            
            this.bookings = data;
            this.renderBookings();
            
        } catch (error) {
            console.error('Error fetching bookings:', error);
        }
    },

    checkChanges(oldBookings, newBookings) {
        // Simple heuristic to show toasts if someone else booked something today
        const oldIds = new Set(oldBookings.map(b => b.id));
        const newItems = newBookings.filter(b => !oldIds.has(b.id) && b.type === 'booking');
        
        if (newItems.length > 0) {
            App.showToast('Alcuni slot sono stati appena prenotati.', 'info');
        }
    },

    renderBookings() {
        // Clear old slots
        document.querySelectorAll('.slot').forEach(el => el.remove());
        
        const dateStr = this.formatDateForApi(this.currentDate);
        
        this.bookings.forEach(booking => {
            // Calculate position
            const bStart = new Date(booking.start_time);
            
            // Only show bookings for current view date
            if (this.formatDateForApi(bStart) !== dateStr) return;
            
            const hour = bStart.getHours();
            
            // Find cell
            const cell = document.querySelector(`.calendar-cell[data-resource-id="${booking.resource_id}"][data-hour="${hour}"]`);
            if (!cell) return;
            
            const slot = document.createElement('div');
            // 'booking' or 'lock'
            if (booking.type === 'lock') {
                slot.className = 'slot locked';
                slot.textContent = 'In approvazione';
            } else {
                slot.className = 'slot booked';
                slot.textContent = `Prenotato`;
                if (booking.is_mine) {
                    slot.classList.add('my-booking');
                    slot.textContent = 'Mia Prenotazione';
                }
            }
            
            // Prevent click on slot from propagating to cell
            slot.addEventListener('click', (e) => e.stopPropagation());
            
            cell.appendChild(slot);
        });
        
        // Tag available cells visually (optional, just css styling over empty cells usually enough)
        document.querySelectorAll('.calendar-cell[data-hour]').forEach(cell => {
            if (!cell.querySelector('.slot')) {
                const s = document.createElement('div');
                s.className = 'slot available';
                s.textContent = 'Libero';
                // Pass click through
                s.style.pointerEvents = 'none';
                cell.appendChild(s);
            }
        });
    },

    handleSlotClick(resource, hour) {
        // Build datetime
        const dateStr = this.formatDateForApi(this.currentDate);
        const startTime = `${dateStr} ${hour.toString().padStart(2, '0')}:00:00`;
        const endTimeStr = `${dateStr} ${(hour+1).toString().padStart(2, '0')}:00:00`;
        
        BookingFlow.initiateLock(resource, startTime, endTimeStr);
    },

    startPolling() {
        if (this.intervalId) clearInterval(this.intervalId);
        this.intervalId = setInterval(() => this.fetchBookings(), this.pollInterval);
    }
};

const App = {
    showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        let icon = 'ℹ️';
        if (type === 'success') icon = '✅';
        if (type === 'error') icon = '❌';
        if (type === 'warning') icon = '⚠️';
        
        toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
        container.appendChild(toast);
        
        // Trigger reflow
        void toast.offsetWidth;
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
};
