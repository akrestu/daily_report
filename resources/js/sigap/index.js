// Registrasi komponen UI SiGAP. Dipanggil dari app.js sebelum Alpine.start().
import sgUploader from './uploader';
import sgReportForm from './report-form';
import sgComments from './comments';
import sgQueue from './queue';
import notificationStore from './notifications';
import { toast, showFlashes } from './toast';
import { toggleSidebar, setTheme, currentTheme, stackTables } from './shell';

export function registerSigap(Alpine) {
    Alpine.data('sgUploader', sgUploader);
    Alpine.data('sgReportForm', sgReportForm);
    Alpine.data('sgComments', sgComments);
    Alpine.data('sgQueue', sgQueue);
    Alpine.store('notif', notificationStore());
    Alpine.store('theme', {
        mode: currentTheme(),
        set(mode) {
            this.mode = mode;
            setTheme(mode);
        },
    });

    window.sgToast = toast;
    window.sgToggleSidebar = toggleSidebar;

    const boot = () => {
        showFlashes();
        stackTables();
        if (window.__sgRoutes?.notifList) Alpine.store('notif').start();
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}
