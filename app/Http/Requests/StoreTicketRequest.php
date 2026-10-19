<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi pembuatan tiket. Pemohon tamu (form publik) wajib NIK karyawan aktif;
 * user yang login memakai data profilnya sendiri.
 */
class StoreTicketRequest extends FormRequest
{
    public ?Employee $employee = null;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * User login yang menjadi pemohon. Form publik selalu memakai NIK walau
     * pengirimnya sedang login, agar tidak tertolak "NIK tidak boleh diisi".
     */
    public function accountUser(): ?User
    {
        return $this->routeIs('tickets.public.store') ? null : $this->user();
    }

    public function rules(): array
    {
        return [
            'nik' => $this->accountUser() ? 'prohibited' : 'required|string|max:30',
            'requester_phone' => 'nullable|string|max:30',
            'job_site_id' => 'nullable|exists:job_sites,id',
            'type' => ['required', Rule::in(array_keys(Ticket::TYPES))],
            'category_id' => 'required|exists:ticket_categories,id',
            'supervisor_id' => ['nullable', Rule::exists('employees', 'id')->where('is_active', true)],
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'impact' => ['required', Rule::in(array_keys(Ticket::IMPACTS))],
            'urgency' => ['required', Rule::in(array_keys(Ticket::URGENCIES))],
            'attachment' => 'nullable|file|max:5120|mimes:jpeg,jpg,png,gif,pdf,doc,docx,xls,xlsx',
            'attachment_token' => 'nullable|string|size:40',
        ];
    }

    /** Lokasi & kunci sesi lampiran yang ditahan saat validasi gagal */
    private const HOLD_DIR = 'tmp/ticket-attachments';

    private const HOLD_SESSION = 'ticket_attachment_hold';

    /**
     * Validasi gagal tetapi lampiran valid: tahan file di server agar pemohon tidak perlu
     * memilih ulang (browser tidak bisa mengisi ulang input file).
     */
    protected function failedValidation(ValidatorContract $validator)
    {
        $this->holdAttachment($validator);

        parent::failedValidation($validator);
    }

    private function holdAttachment(ValidatorContract $validator): void
    {
        $file = $this->file('attachment');

        if (! $file) {
            // Lampiran yang sudah ditahan tetap dibawa ke percobaan berikutnya
            if ($held = $this->heldAttachment()) {
                session()->flash('held_attachment', ['token' => $this->input('attachment_token'), 'name' => $held['name']]);
            }

            return;
        }

        if ($validator->errors()->has('attachment') || ! $file->isValid()) {
            return;
        }

        $this->pruneHeldAttachments();

        $token = Str::random(40);
        $path = $file->storeAs(self::HOLD_DIR, $token.'.'.($file->extension() ?: 'bin'), 'local');
        session()->put(self::HOLD_SESSION.'.'.$token, ['path' => $path, 'name' => $file->getClientOriginalName()]);
        session()->flash('held_attachment', ['token' => $token, 'name' => $file->getClientOriginalName()]);
    }

    /** Lampiran tertahan milik sesi ini (token dari form harus cocok dengan sesi) */
    private function heldAttachment(): ?array
    {
        $token = $this->input('attachment_token');
        if (! is_string($token) || strlen($token) !== 40 || ! ctype_alnum($token)) {
            return null;
        }

        $held = session(self::HOLD_SESSION.'.'.$token);

        return $held && Storage::disk('local')->exists($held['path']) ? $held : null;
    }

    /** Pindahkan lampiran tertahan ke penyimpanan lampiran tiket */
    private function takeHeldAttachment(): array
    {
        $held = $this->heldAttachment();
        if (! $held) {
            return [null, null];
        }

        $extension = pathinfo($held['path'], PATHINFO_EXTENSION) ?: 'bin';
        $path = 'attachments/ticket_'.uniqid().'_'.time().'.'.$extension;
        Storage::disk('attachments')->writeStream($path, Storage::disk('local')->readStream($held['path']));
        Storage::disk('local')->delete($held['path']);
        session()->forget(self::HOLD_SESSION.'.'.$this->input('attachment_token'));

        return [$path, $held['name']];
    }

    /** Scheduler tidak jalan di produksi, jadi file tertahan > 1 hari dibersihkan di sini */
    private function pruneHeldAttachments(): void
    {
        $disk = Storage::disk('local');
        foreach ($disk->files(self::HOLD_DIR) as $old) {
            if ($disk->lastModified($old) < now()->subDay()->getTimestamp()) {
                $disk->delete($old);
            }
        }
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $category = TicketCategory::find($this->input('category_id'));
                if ($category?->requires_approval && ! $this->filled('supervisor_id')) {
                    $validator->errors()->add('supervisor_id', 'Kategori "'.$category->name.'" memerlukan persetujuan atasan. Pilih atasan Anda.');
                }

                if ($this->accountUser() || $validator->errors()->has('nik')) {
                    return;
                }

                $this->employee = Employee::findActiveByNik($this->input('nik'));

                if (! $this->employee) {
                    $validator->errors()->add('nik', 'NIK tidak terdaftar atau tidak aktif. Hubungi tim IT jika NIK Anda belum terdaftar.');
                } elseif ((int) $this->input('supervisor_id') === $this->employee->id) {
                    $validator->errors()->add('supervisor_id', 'Atasan tidak boleh diri sendiri.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi.',
            'type.required' => 'Jenis tiket wajib dipilih.',
            'category_id.required' => 'Kategori permintaan wajib dipilih.',
            'title.required' => 'Judul permintaan wajib diisi.',
            'description.required' => 'Deskripsi detail wajib diisi.',
            'impact.required' => 'Dampak wajib dipilih.',
            'urgency.required' => 'Urgensi wajib dipilih.',
            'attachment.max' => 'Ukuran file maksimal 5 MB.',
            'attachment.mimes' => 'Format file tidak didukung.',
        ];
    }

    /**
     * Data pemohon diambil dari master (bukan input bebas) agar tidak bisa dipalsukan.
     */
    public function ticketAttributes(): array
    {
        $user = $this->accountUser();
        // File baru menggantikan lampiran yang ditahan dari percobaan sebelumnya
        [$path, $originalName] = $this->hasFile('attachment')
            ? Ticket::storeAttachment($this->file('attachment'))
            : $this->takeHeldAttachment();

        $requester = $user
            ? [
                'requester_user_id' => $user->id,
                'requester_name' => $user->name,
                'requester_employee_id' => $user->user_id,
                'requester_department' => $user->department?->name ?? '-',
            ]
            : [
                'employee_id' => $this->employee->id,
                'requester_name' => $this->employee->name,
                'requester_employee_id' => $this->employee->nik,
                'requester_department' => $this->employee->department?->name ?? '-',
            ];

        $requiresApproval = (bool) TicketCategory::find($this->input('category_id'))?->requires_approval;

        return $requester + [
            'supervisor_employee_id' => $requiresApproval ? $this->input('supervisor_id') : null,
            'approval_status' => $requiresApproval ? Ticket::APPROVAL_WAITING_UPLOAD : null,
            'requester_phone' => $this->input('requester_phone') ?: $this->employee?->phone,
            'job_site_id' => $this->input('job_site_id') ?: ($user?->job_site_id ?? $this->employee?->job_site_id),
            'type' => $this->input('type'),
            'category_id' => $this->input('category_id'),
            'title' => $this->input('title'),
            'description' => $this->input('description'),
            'impact' => $this->input('impact'),
            'urgency' => $this->input('urgency'),
            'priority' => Ticket::priorityFor($this->input('impact'), $this->input('urgency')),
            'attachment_path' => $path,
            'attachment_original_name' => $originalName,
        ];
    }
}
