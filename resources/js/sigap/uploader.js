// Alpine data untuk lampiran (kamera + galeri → input form).
// Mode array : satu <input x-ref="field" name="x[]" multiple> berisi semua file.
// Mode names : satu file per nama field, mis. ['reports[0][attachment]', 'reports[0][attachment_2]', ...]
//              → <input type="file" data-slot="k"> per nama (lihat daily-reports/create).
export default function sgUploader({ max = 3, maxMb = 5, names = null } = {}) {
    return {
        max: names ? names.length : max,
        maxMb,
        names,
        files: [],
        error: '',

        add(event) {
            const picked = Array.from(event.target.files || []);
            event.target.value = '';
            this.error = '';

            for (const file of picked) {
                if (this.files.length >= this.max) {
                    this.error = `Maksimal ${this.max} lampiran.`;
                    break;
                }
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
