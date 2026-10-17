// Alpine data untuk lampiran (kamera + galeri → input form).
// Mode array : satu <input x-ref="field" name="x[]" multiple> berisi semua file.
// Mode names : satu file per nama field, mis. ['reports[0][attachment]', 'reports[0][attachment_2]', ...]
//              → <input type="file" data-slot="k"> per nama (lihat daily-reports/create).
// Foto JPEG/WebP dikompres di klien (sisi terpanjang 1920px, kualitas 0,8) sebelum dicek ukurannya: hemat kuota,
// dan foto kamera 8 MB tetap bisa diunggah. Server tetap me-resize lewat Intervention.
const MAX_SIDE = 1920;
const QUALITY = 0.8;
const COMPRESS_FROM = 300 * 1024;

export async function compressImage(file) {
    if (!['image/jpeg', 'image/webp'].includes(file.type) || file.size < COMPRESS_FROM || !window.createImageBitmap) return file;
    try {
        const bitmap = await createImageBitmap(file);
        const scale = Math.min(1, MAX_SIDE / Math.max(bitmap.width, bitmap.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(bitmap.width * scale);
        canvas.height = Math.round(bitmap.height * scale);
        canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
        bitmap.close?.();
        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', QUALITY));
        if (!blob || blob.size >= file.size) return file;
        return new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg', lastModified: file.lastModified });
    } catch (e) {
        return file; // format tidak bisa dibaca browser (mis. HEIC): kirim apa adanya
    }
}

export default function sgUploader({ max = 3, maxMb = 5, names = null } = {}) {
    return {
        max: names ? names.length : max,
        maxMb,
        names,
        files: [],
        error: '',
        busy: false,

        async add(event) {
            const picked = Array.from(event.target.files || []);
            event.target.value = '';
            this.error = '';
            this.busy = true;

            for (const original of picked) {
                if (this.files.length >= this.max) {
                    this.error = `Maksimal ${this.max} lampiran.`;
                    break;
                }
                const file = await compressImage(original);
                if (file.size > this.maxMb * 1024 * 1024) {
                    this.error = `${file.name} lebih dari ${this.maxMb} MB.`;
                    continue;
                }
                this.files.push({
                    key: `${file.name}-${file.size}-${Date.now()}-${Math.random()}`,
                    file,
                    preview: file.type.startsWith('image/') ? URL.createObjectURL(file) : null,
                });
            }

            this.busy = false;
            this.sync();
        },

        remove(index) {
            const [item] = this.files.splice(index, 1);
            if (item?.preview) URL.revokeObjectURL(item.preview);
            this.error = '';
            this.sync();
        },

        sync() {
            if (this.names) {
                this.$nextTick(() => {
                    this.$root.querySelectorAll('input[data-slot]').forEach((input) => {
                        const transfer = new DataTransfer();
                        const item = this.files[Number(input.dataset.slot)];
                        if (item) transfer.items.add(item.file);
                        input.files = transfer.files;
                    });
                });
                return;
            }
            const transfer = new DataTransfer();
            this.files.forEach((item) => transfer.items.add(item.file));
            this.$refs.field.files = transfer.files;
        },
    };
}

// Satu slot lampiran yang sudah ada isinya (Edit Laporan): tampilkan file lama, "Ganti" lewat kamera/galeri.
// File baru masuk ke <input x-ref="field" name="attachment|attachment_2|attachment_3"> dan menggantikan file lama saat disimpan.
export function sgSlot({ maxMb = 5 } = {}) {
    return {
        picked: null,
        preview: null,
        busy: false,
        error: '',

        async pick(event) {
            const original = event.target.files?.[0];
            event.target.value = '';
            if (!original) return;
            this.error = '';
            this.busy = true;
            const file = await compressImage(original);
            this.busy = false;
            if (file.size > maxMb * 1024 * 1024) {
                this.error = `${file.name} lebih dari ${maxMb} MB.`;
                return;
            }
            const transfer = new DataTransfer();
            transfer.items.add(file);
            this.$refs.field.files = transfer.files;
            if (this.preview) URL.revokeObjectURL(this.preview);
            this.preview = file.type.startsWith('image/') ? URL.createObjectURL(file) : null;
            this.picked = file.name;
        },

        undo() {
            this.$refs.field.value = '';
            if (this.preview) URL.revokeObjectURL(this.preview);
            this.preview = null;
            this.picked = null;
            this.error = '';
        },
    };
}
