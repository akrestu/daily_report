// Registrasi komponen UI SiGAP. Dipanggil dari app.js sebelum Alpine.start().
import sgUploader, { sgSlot } from './uploader';
import sgReportForm from './report-form';
import sgComments from './comments';
import sgQueue from './queue';
import sgSearch from './search';
import sgAdminList from './admin-list';
import notificationStore from './notifications';
import outboxStore from './outbox';
import { toast, showFlashes } from './toast';
import { toggleSidebar, setTheme, currentTheme, stackTables } from './shell';
import { infiniteLists, pullToRefresh } from './infinite';
import { themedChart } from './charts';
import { promptInstall } from './pwa';
import { registerConfirm } from './confirm';

export function registerSigap(Alpine) {
    Alpine.data('sgUploader', sgUploader);
    Alpine.data('sgSlot', sgSlot);
    Alpine.data('sgReportForm', sgReportForm);
    Alpine.data('sgComments', sgComments);
    Alpine.data('sgQueue', sgQueue);
    Alpine.data('sgSearch', sgSearch);
    Alpine.data('sgAdminList', sgAdminList);
    Alpine.store('notif', notificationStore());
    Alpine.store('outbox', outboxStore());
    Alpine.store('theme', {
        mode: currentTheme(),
        set(mode) {
            this.mode = mode;
            setTheme(mode);
        },
    });

    window.sgToast = toast;
    window.sgToggleSidebar = toggleSidebar;
    window.sgChart = themedChart;
    window.sgInstall = promptInstall;
    registerConfirm();

    const boot = () => {
        showFlashes();
        stackTables();
        infiniteLists();
        pullToRefresh();
        if (window.__sgRoutes?.notifList) Alpine.store('notif').start();
        if (window.__sgUserId) Alpine.store('outbox').start();
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}
