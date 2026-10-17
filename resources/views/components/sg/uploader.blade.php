{{--
    Lampiran: "Ambil Foto" (kamera belakang) atau "Galeri / File", maks :max file, thumbnail + hapus.
    File dikumpulkan ke satu <input name="{name}[]"> agar form biasa tetap terkirim. Logika: resources/js/sigap/uploader.js
--}}
@props(['name' => 'attachments', 'max' => 3, 'accept' => 'image/*,.pdf,.doc,.docx,.xls,.xlsx', 'maxMb' => 5, 'label' => 'Lampiran'])
<div {{ $attributes->class(['sg-uploader']) }} x-data="sgUploader({ max: {{ (int) $max }}, maxMb: {{ (int) $maxMb }} })">
    <div class="form-label fw-semibold">{{ $label }} <span class="text-muted fw-normal">(maks. {{ $max }})</span></div>

    <input type="file" name="{{ $name }}[]" multiple class="d-none" x-ref="field" tabindex="-1" aria-hidden="true">
    <input type="file" accept="image/*" capture="environment" class="d-none" x-ref="camera" tabindex="-1" aria-hidden="true" @change="add($event)">
    <input type="file" accept="{{ $accept }}" multiple class="d-none" x-ref="gallery" tabindex="-1" aria-hidden="true" @change="add($event)">

    <div class="sg-uploader__grid" x-show="files.length" x-cloak>
        <template x-for="(item, index) in files" :key="item.key">
            <div class="sg-uploader__item">
                <template x-if="item.preview"><img :src="item.preview" :alt="item.file.name"></template>
                <template x-if="!item.preview">
                    <div class="sg-uploader__file"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><span x-text="item.file.name"></span></div>
                </template>
                <button type="button" class="sg-uploader__remove" @click="remove(index)" :aria-label="'Hapus ' + item.file.name">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
        </template>
    </div>

    <div class="sg-uploader__actions" x-show="files.length < max">
        <button type="button" class="btn btn-outline-secondary" @click="$refs.camera.click()"><i class="bi bi-camera" aria-hidden="true"></i> Ambil Foto</button>
        <button type="button" class="btn btn-outline-secondary" @click="$refs.gallery.click()"><i class="bi bi-images" aria-hidden="true"></i> Galeri / File</button>
    </div>
    <div class="sg-uploader__hint" :class="{ 'text-danger': error }" aria-live="polite"
         x-text="error || 'Foto, PDF, Word, atau Excel. Maks. {{ $maxMb }} MB per file.'"></div>
</div>
