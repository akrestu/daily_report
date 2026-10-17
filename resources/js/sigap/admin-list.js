// Daftar master data admin (departemen, job site, section, pengguna).
// - Pilih banyak → bar hapus massal (sheet konfirmasi berisi input ids[] dari `selected`)
// - edit(item) / remove(item) → isi `item` lalu buka sheet; form memakai :action="url(item.id)"
export default function sgAdminList({ base = '', ids = [] } = {}) {
    return {
        ids,
        selected: [],
        item: {},
        mode: 'create',

        get allSelected() {
            return this.ids.length > 0 && this.ids.every((id) => this.selected.includes(id));
        },
        isSelected(id) {
            return this.selected.includes(id);
        },
        toggle(id) {
            this.selected = this.isSelected(id) ? this.selected.filter((x) => x !== id) : [...this.selected, id];
        },
        toggleAll() {
            this.selected = this.allSelected ? [] : [...this.ids];
        },
        url(id) {
            return `${base}/${id}`;
        },
        sheet(id) {
            window.bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show();
        },
        create(defaults = {}) {
            this.mode = 'create';
            this.item = { ...defaults };
            this.sheet('item-sheet');
        },
        edit(item) {
            this.mode = 'edit';
            this.item = { ...item };
            this.sheet('item-sheet');
        },
        remove(item) {
            this.item = { ...item };
            this.sheet('delete-sheet');
        },
    };
}
