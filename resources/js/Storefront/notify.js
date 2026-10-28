import Swal from 'sweetalert2';
import './toast.css';

export function notify(title, icon = 'info') {
    return Swal.fire({
        toast: true, position: 'top', title, icon,
        showConfirmButton: false, showCloseButton: true,
        timer: 4200, timerProgressBar: true,
        customClass: { popup: 'store-toast', timerProgressBar: 'store-toast-progress' },
        showClass: { popup: 'store-toast-enter' },
        hideClass: { popup: 'store-toast-leave' },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
            toast.setAttribute('role', icon === 'error' ? 'alert' : 'status');
        },
    });
}
