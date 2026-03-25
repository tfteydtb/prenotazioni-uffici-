# prenotazioni-uffici galilux progetto tps
Sistema web per la gestione e prenotazione di risorse condivise (sale riunioni, postazioni e attrezzature), sviluppato con PHP, MySQL e JavaScript. L’applicazione consente agli utenti di verificare la disponibilità in tempo reale, evitare conflitti tramite controlli automatici e lock temporanei, gestire le proprie prenotazioni e accedere a report sull’utilizzo delle risorse. Progettato per funzionare in ambiente locale con XAMPP, presenta un’architettura semplice e modulare facilmente estendibile.
📌 Resource Booking System
Sistema web completo per la gestione e prenotazione di risorse condivise (sale riunioni, postazioni di lavoro e attrezzature), sviluppato con PHP, MySQL e JavaScript.
🚀 Panoramica
Questa applicazione permette agli utenti di prenotare risorse in modo semplice ed efficiente, evitando conflitti grazie a un sistema di controllo disponibilità e lock temporanei.
Il progetto è progettato per essere eseguito in ambiente locale utilizzando XAMPP ed è strutturato in modo modulare per facilitare manutenzione ed espansione.
⚙️ Tecnologie utilizzate
Backend: PHP 8
Database: MySQL (phpMyAdmin)
Frontend: HTML5, CSS3, JavaScript
Comunicazione: AJAX
Server: Apache (XAMPP)
✨ Funzionalità principali
📋 Gestione risorse (sale, desk, attrezzature)
📅 Sistema di prenotazione con selezione data/ora
🚫 Controllo automatico dei conflitti
🔒 Lock temporaneo (2 minuti) per evitare doppie prenotazioni
🔄 Aggiornamenti tramite AJAX
👤 Dashboard utente con prenotazioni personali
❌ Cancellazione prenotazioni
⏳ Sistema di waitlist (lista di attesa)
📊 Report utilizzo risorse
🗄 Struttura Database
Tabelle principali:
users → utenti
resources → risorse disponibili
bookings → prenotazioni
locks → blocchi temporanei
waitlist → lista di attesa
🧠 Logica di funzionamento
L’utente seleziona una risorsa
Sceglie data e orario
Il sistema verifica la disponibilità
Se disponibile → crea un lock temporaneo
L’utente conferma la prenotazione
Il lock diventa prenotazione definitiva
🔐 Sicurezza
Validazione input lato server
Prepared statements (anti SQL injection)
Gestione sessioni utente
📁 Struttura del progetto
resource_booking/
config/
models/
pages/
actions/
js/
css/
database/
▶️ Installazione
Copia il progetto in:
xampp/htdocs/
Avvia:
Apache
MySQL
Importa il database:
Apri phpMyAdmin
Importa database/schema.sql
Apri nel browser:
http://localhost/resource_booking/pages/index.php
📈 Possibili miglioramenti
🔐 Sistema di login completo
📆 Calendario avanzato stile Google Calendar
🔔 Notifiche in tempo reale (WebSocket)
📊 Dashboard con grafici
📱 Interfaccia responsive mobile
👨‍💻 Autore
Progetto sviluppato per scopi didattici e dimostrativi.
