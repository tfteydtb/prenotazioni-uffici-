// js/booking.js

const BookingFlow = {
    currentLockId: null,
    lockTimerInterval: null,
    lockDuration: 120, // 2 minutes in seconds

    async initiateLock(resource, startTime, endTime) {
        try {
            const formData = new URLSearchParams();
            formData.append('resource_id', resource.id);
            formData.append('start_time', startTime);

            const response = await fetch('actions/create_lock.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.currentLockId = data.lock_id;
                this.showBookingModal(resource, startTime, endTime);
            } else {
                App.showToast(data.message, 'error');
                
                // If failed, maybe offer waitlist
                if (data.message.includes('occupata')) {
                    this.offerWaitlist(resource.id);
                }
            }
        } catch (error) {
            console.error('Error initiating lock:', error);
            App.showToast('Errore di connessione', 'error');
        }
    },

    showBookingModal(resource, startTime, endTime) {
        const modal = document.getElementById('booking-modal');
        if (!modal) return;

        // Populate data
        document.getElementById('modal-resource-name').textContent = resource.nome;
        document.getElementById('modal-time').textContent = `${new Date(startTime).toLocaleString('it-IT')} - ${new Date(endTime).toLocaleTimeString('it-IT')}`;
        
        // Store technical details in form hidden fields if needed or JS state
        this.pendingBooking = {
            resource_id: resource.id,
            start_time: startTime,
            end_time: endTime
        };

        // Start timer
        this.startLockTimer();

        // Show modal
        modal.classList.add('active');
        
        // Pause background polling while modal open so it doesn't distract
        if (Calendar.intervalId) clearInterval(Calendar.intervalId);
    },

    closeBookingModal() {
        const modal = document.getElementById('booking-modal');
        if (modal) modal.classList.remove('active');
        
        this.currentLockId = null;
        this.pendingBooking = null;
        if (this.lockTimerInterval) clearInterval(this.lockTimerInterval);
        
        // Resume polling
        Calendar.startPolling();
    },

    startLockTimer() {
        let timeLeft = this.lockDuration;
        const bar = document.getElementById('lock-timer-bar');
        const text = document.getElementById('lock-timer-text');
        
        if (this.lockTimerInterval) clearInterval(this.lockTimerInterval);
        
        this.lockTimerInterval = setInterval(() => {
            timeLeft--;
            
            // Update UI
            const percentage = (timeLeft / this.lockDuration) * 100;
            if (bar) bar.style.width = `${percentage}%`;
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            if (text) text.textContent = `Tempo rimasto: ${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            // Change color warning
            if (timeLeft < 30 && bar) bar.style.background = 'var(--accent-color)';
            else if (bar) bar.style.background = 'var(--secondary-color)';

            if (timeLeft <= 0) {
                clearInterval(this.lockTimerInterval);
                this.closeBookingModal();
                App.showToast('Tempo scaduto. Il lock è stato rilasciato.', 'warning');
            }
        }, 1000);
    },

    async confirmBooking() {
        if (!this.pendingBooking || !this.currentLockId) return;

        try {
            const formData = new URLSearchParams();
            formData.append('resource_id', this.pendingBooking.resource_id);
            formData.append('start_time', this.pendingBooking.start_time);
            formData.append('end_time', this.pendingBooking.end_time);
            formData.append('lock_id', this.currentLockId);

            const response = await fetch('actions/create_booking.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                App.showToast(data.message, 'success');
                this.closeBookingModal();
                Calendar.fetchBookings(); // Refresh UI instantly
            } else {
                App.showToast(data.message, 'error');
                this.closeBookingModal();
            }
        } catch (error) {
            console.error('Error confirming booking:', error);
            App.showToast('Errore di connessione', 'error');
        }
    },

    async cancelBooking(bookingId) {
        if (!confirm('Sei sicuro di voler cancellare questa prenotazione?')) return;

        try {
            const formData = new URLSearchParams();
            formData.append('booking_id', bookingId);

            const response = await fetch('actions/cancel_booking.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                App.showToast(data.message, 'success');
                // Reload page or refresh list
                window.location.reload();
            } else {
                App.showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Error cancelling booking:', error);
            App.showToast('Errore di connessione', 'error');
        }
    },

    offerWaitlist(resourceId) {
        // Find existing waitlist container or create toast action
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) return;
        
        const lw = document.createElement('div');
        lw.className = 'toast warning show';
        lw.style.flexDirection = 'column';
        lw.style.alignItems = 'flex-start';
        lw.innerHTML = `
            <div>Risorsa non disponibile. Vuoi iscriverti alla lista d'attesa?</div>
            <button class="btn btn-secondary mt-1" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="BookingFlow.joinWaitlist(${resourceId}); this.parentElement.remove();">Iscriviti</button>
        `;
        toastContainer.appendChild(lw);
        
        setTimeout(() => {
            if (lw.parentNode) lw.remove();
        }, 10000); // give them 10s to click
    },

    async joinWaitlist(resourceId) {
        try {
            const formData = new URLSearchParams();
            formData.append('resource_id', resourceId);

            const response = await fetch('actions/join_waitlist.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                App.showToast(data.message, 'success');
            } else {
                App.showToast(data.message, 'warning');
            }
        } catch (error) {
            console.error('Waitlist error:', error);
            App.showToast('Errore di connessione', 'error');
        }
    }
};

// Initialization for modal buttons
document.addEventListener('DOMContentLoaded', () => {
    const confirmBtn = document.getElementById('btn-confirm-booking');
    const cancelBtn = document.getElementById('btn-cancel-modal');
    const closeBtn = document.getElementById('modal-close');
    
    if (confirmBtn) confirmBtn.addEventListener('click', () => BookingFlow.confirmBooking());
    if (cancelBtn) cancelBtn.addEventListener('click', () => BookingFlow.closeBookingModal());
    if (closeBtn) closeBtn.addEventListener('click', () => BookingFlow.closeBookingModal());
});