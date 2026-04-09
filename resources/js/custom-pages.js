import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import '../css/custom-pages.css';

window.Alpine = Alpine;
window.Swal = Swal;

document.addEventListener('DOMContentLoaded', () => {
    Alpine.start();
});
